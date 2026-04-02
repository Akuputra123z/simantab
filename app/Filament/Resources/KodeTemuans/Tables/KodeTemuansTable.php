<?php

namespace App\Filament\Resources\KodeTemuans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KodeTemuansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                               TextColumn::make('kode_numerik')
                    ->label('Kode Resmi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('kel')
                    ->label('Kel.')
                    ->formatStateUsing(fn ($state) => match ((int) $state) {
                        1 => 'Ketidakpatuhan',
                        2 => 'SPI',
                        3 => '3E',
                        default => '-',
                    })
                    ->badge()
                    ->color(fn ($record) => match ((int) ($record->kel ?? 0)) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('sub_kelompok')
                    ->label('Sub Kelompok')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi Jenis Temuan')
                    ->searchable()
                    ->limit(65)
                    ->tooltip(fn ($record) => $record->deskripsi)
                    ->wrap(),

                TextColumn::make('alternatif_rekom')
                    ->label('Alt. Rekom')
                    ->formatStateUsing(fn ($state) => is_array($state)
                        ? implode(', ', array_map(fn ($n) => sprintf('%02d', $n), $state))
                        : '-'
                    )
                    ->color('success'),

                TextColumn::make('temuans_count')
                    ->label('Temuan')
                    ->counts('temuans')
                    ->badge()
                    ->color('danger'),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
