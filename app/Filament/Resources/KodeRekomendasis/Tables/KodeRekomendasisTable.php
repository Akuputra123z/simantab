<?php

namespace App\Filament\Resources\KodeRekomendasis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KodeRekomendasisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('kode_numerik')
                    ->label('Kode Numerik')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn ($state) => match ($state) {
                        'Administratif' => 'primary',
                        'Keuangan'      => 'success',
                        'Kinerja'       => 'warning',
                        default         => 'gray',
                    }),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])

            ->defaultSort('kode_numerik', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}