<?php

namespace App\Filament\Resources\Lhps\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LhpsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            // Eager Load untuk performa agar tidak N+1 Query
            ->modifyQueryUsing(fn ($query) => $query->with([
                'statistik',
                'auditAssignment.unitDiperiksa',
                'auditAssignment.auditProgram',
            ]))
            ->columns([
                TextColumn::make('nomor_lhp')
                    ->label('Nomor LHP')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('auditAssignment.unitDiperiksa.nama_unit')
                    ->label('Unit Diperiksa')
                    ->searchable()
                    ->limit(35)
                    ->description(fn ($record) => $record->auditAssignment?->auditProgram?->nama_program)
                    ->tooltip(fn ($record) => $record->auditAssignment?->unitDiperiksa?->nama_unit),

                TextColumn::make('auditAssignment.unitDiperiksa.kategori')
                    ->label('Kat.')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'SKPD'      => 'primary',
                        'Sekolah'   => 'success',
                        'Puskesmas' => 'warning',
                        'Desa'      => 'info',
                        'BLUD'      => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('tanggal_lhp')
                    ->label('Tanggal LHP')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('irban')
                    ->label('Irban')
                    ->badge()
                    ->color('info'),

                // Kolom Temuan
                TextColumn::make('statistik.total_temuan')
                    ->label('Temuan')
                    ->badge()
                    // Jika ada temuan ( > 0 ) warna merah, jika 0 warna abu
                    ->color(fn ($state) => (int)$state > 0 ? 'danger' : 'gray')
                    ->alignCenter()
                    ->default(0),

                // Metrik Utama % TL Gabungan (Flat Weight)
                TextColumn::make('statistik.persen_selesai_gabungan')
                    ->label('% TL')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->default(0)
                    ->color(fn ($state): string => match (true) {
                        (float) $state >= 100 => 'success',
                        (float) $state >= 75  => 'info', // Tambahan warna biru untuk progres tinggi
                        (float) $state >= 40  => 'warning',
                        (float) $state > 0    => 'danger',
                        default               => 'gray',
                    })
                    // Tooltip diperbaiki agar tidak error jika statistik null
                    ->tooltip(fn ($record): string => self::tooltipProgress($record)),
                TextColumn::make('status_batal_keterangan')
    ->label('Keterangan Pembatalan')
    ->limit(50) // optional, agar tidak terlalu panjang
    ->tooltip(fn ($record) => $record->status_batal_keterangan)
    ->sortable()
    ->color(fn ($state) => $state ? 'danger' : 'gray'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft'          => 'gray',
                        'final'          => 'warning',
                        'ditandatangani' => 'success',
                        default          => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? '-')),
            ])

            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'          => 'Draft',
                        'final'          => 'Final',
                        'ditandatangani' => 'Ditandatangani',
                    ]),

                SelectFilter::make('irban')
                    ->label('Irban')
                    ->options([
                        'Irban I'   => 'Irban I',
                        'Irban II'  => 'Irban II',
                        'Irban III' => 'Irban III',
                    ]),

                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        1 => 'Semester I',
                        2 => 'Semester II',
                    ]),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('batalkanStatus')
    ->label('Batalkan Status')
    ->color('danger')
    ->icon('heroicon-o-x-circle')
    ->visible(fn ($record) => 
        ($user = auth()->user()) && 
        ($user->hasRole('kepala_inspektorat') || $user->hasRole('super_admin')) &&
        $record->status !== 'draft'
    )
    ->form([
        Textarea::make('keterangan')
            ->label('Alasan Pembatalan')
            ->required()
            ->maxLength(500),
    ])
    ->action(function ($record, array $data) {
        $record->update([
            'status' => 'draft',
            'status_batal_keterangan' => $data['keterangan'],
            'status_batal_user_id' => auth()->id(),
            'status_batal_at' => now(),
        ]);
    })
    ->requiresConfirmation()
               
                
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    /**
     * Tooltip breakdown — muncul saat hover badge % TL.
     */

    
    private static function tooltipProgress($record): string
    {
        $stat = $record->statistik;
        
        if (! $stat) {
            return 'Belum ada data statistik.';
        }

        $totalRekom = (int) $stat->total_rekomendasi;
        $rekomSelesai = (int) $stat->rekom_selesai;
        $persen = (float) $stat->persen_selesai_gabungan;

        if ($totalRekom === 0) {
            return 'Tidak ada rekomendasi pada LHP ini.';
        }

        $output = "Status: " . ($persen >= 100 ? 'Lunas / Selesai' : 'Dalam Proses');
        $output .= "\nDetail: {$rekomSelesai} dari {$totalRekom} Rekomendasi Selesai";

        // Tambahan info Nilai Uang jika ini temuan kerugian
        if ((float) $stat->total_kerugian > 0) {
            $realisasi = number_format($stat->total_nilai_tl_selesai, 0, ',', '.');
            $target = number_format($stat->total_kerugian, 0, ',', '.');
            $output .= "\nSetoran: Rp {$realisasi} / Rp {$target}";
        }

        return $output;
    }
}