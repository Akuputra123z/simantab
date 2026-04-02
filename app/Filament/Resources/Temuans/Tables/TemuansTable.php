<?php

namespace App\Filament\Resources\Temuans\Tables;

use App\Models\Temuan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TemuansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            // Eager load semua relasi yang dipakai di kolom agar tidak N+1
            // dan agar status_tl selalu fresh dari DB saat tabel dirender
            ->modifyQueryUsing(fn ($query) => $query->with([
                'lhp.auditAssignment.unitDiperiksa',
                'kodeTemuan',
                'recommendations',
            ]))

            ->columns([

                TextColumn::make('lhp.nomor_lhp')
                    ->label('Nomor LHP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lhp.auditAssignment.unitDiperiksa.nama_unit')
                    ->label('Unit Diperiksa')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('kodeTemuan.kode')
                    ->label('Kode')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->kondisi)
                    ->searchable(),

                TextColumn::make('nilai_temuan')
                    ->label('Nilai Temuan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Gunakan kolom DB langsung via sum, bukan accessor PHP
                // agar bisa di-sort dan selalu konsisten dengan DB
                TextColumn::make('total_kerugian')
                    ->label('Total Kerugian')
                    ->money('IDR')
                    ->getStateUsing(fn (Temuan $record): float =>
                        (float) $record->nilai_kerugian_negara
                        + (float) $record->nilai_kerugian_daerah
                        + (float) $record->nilai_kerugian_desa
                        + (float) $record->nilai_kerugian_bos_blud
                    ),

                TextColumn::make('recommendations_count')
                    ->label('Rekom')
                    ->counts('recommendations')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                // status_tl: gunakan $state bukan $record agar Filament
                // membaca langsung dari nilai kolom DB, bukan cache relasi
                TextColumn::make('status_tl')
                    ->label('Status TL')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Temuan::STATUS_BELUM   => 'Belum Ditindaklanjuti',
                        Temuan::STATUS_PROSES  => 'Dalam Proses',
                        Temuan::STATUS_SELESAI => 'Selesai',
                        default                => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Temuan::STATUS_BELUM   => 'danger',
                        Temuan::STATUS_PROSES  => 'warning',
                        Temuan::STATUS_SELESAI => 'success',
                        default                => 'gray',
                    }),
            ])

            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('status_tl')
                    ->label('Status TL')
                    ->options([
                        Temuan::STATUS_BELUM   => 'Belum Ditindaklanjuti',
                        Temuan::STATUS_PROSES  => 'Dalam Proses',
                        Temuan::STATUS_SELESAI => 'Selesai',
                    ]),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}