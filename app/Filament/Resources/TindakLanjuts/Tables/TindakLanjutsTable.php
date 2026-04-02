<?php

namespace App\Filament\Resources\TindakLanjuts\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TindakLanjutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            // Eager load semua relasi yang dipakai di kolom
            ->modifyQueryUsing(fn ($query) => $query->with([
                'recommendation.temuan.lhp',
                'recommendation',
                'attachments',
                 'cicilans',
            ]))

            ->columns([

                TextColumn::make('recommendation.temuan.lhp.nomor_lhp')
                    ->label('Nomor LHP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_penyelesaian')
                    ->label('Metode')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'setor_kas'              => 'success',
                        'pengembalian_barang'    => 'info',
                        'perbaikan_administrasi' => 'warning',
                        'cicilan'                => 'primary',
                        default                  => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'setor_kas'              => 'Setor Kas',
                        'pengembalian_barang'    => 'Pengembalian Barang',
                        'perbaikan_administrasi' => 'Perbaikan Administrasi',
                        'cicilan'                => 'Cicilan',
                        default                  => $state,
                    }),

                // Progress: untuk uang pakai progress(), untuk barang/admin
                // pakai status_verifikasi = lunas (bukan cek lampiran)
                TextColumn::make('progress_persen')
    ->label('% Progress')
    ->getStateUsing(function ($record): string {
        $rekom = $record->recommendation;
        if (! $rekom) return '0%';

        // ✅ Jika nilai_rekom = 0, fallback ke status_verifikasi
        if ($rekom->isUang()) {
            $totalRekom = (float) $rekom->nilai_rekom;

            if ($totalRekom <= 0) {
                // nilai_rekom 0 tapi jenis uang — fallback ke status verifikasi
                return $record->status_verifikasi === 'lunas' ? '100%' : '0%';
            }

            $terbayar = (float) $record->total_terbayar;
            $progress = ($terbayar / $totalRekom) * 100;
            return number_format(min($progress, 100), 2) . '%';
        }

        // Non-uang: cukup cek status_verifikasi
        return $record->status_verifikasi === 'lunas' ? '100%' : '0%';
    }),

                TextColumn::make('status_verifikasi')
                    ->label('Verifikasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lunas'               => 'success',
                        'berjalan'            => 'info',
                        'menunggu_verifikasi' => 'warning',
                        'ditolak'             => 'danger',
                        default               => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'lunas'               => 'Lunas',
                        'berjalan'            => 'Berjalan',
                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                        'ditolak'             => 'Ditolak',
                        default               => $state,
                    }),

                TextColumn::make('total_terbayar')
                    ->label('Realisasi (Rp)')
                    ->money('IDR')
                    ->summarize(Sum::make()->money('IDR')),

                TextColumn::make('sisa_belum_bayar')
                    ->label('Sisa (Rp)')
                    ->money('IDR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

            ])

            ->filters([
                // TrashedFilter::make(),

                SelectFilter::make('jenis_penyelesaian')
                    ->label('Metode')
                    ->options([
                        'setor_kas'              => 'Setor Kas',
                        'pengembalian_barang'    => 'Pengembalian Barang',
                        'perbaikan_administrasi' => 'Perbaikan Administrasi',
                        'cicilan'                => 'Cicilan',
                    ]),

                SelectFilter::make('status_verifikasi')
                    ->label('Status Verifikasi')
                    ->options([
                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                        'berjalan'            => 'Berjalan',
                        'lunas'               => 'Lunas',
                        'ditolak'             => 'Ditolak',
                    ]),

                TernaryFilter::make('is_cicilan')
                    ->label('Hanya Cicilan'),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ]);
    }
}