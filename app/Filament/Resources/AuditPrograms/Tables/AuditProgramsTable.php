<?php

namespace App\Filament\Resources\AuditPrograms\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

class AuditProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tahun', 'desc')

            // ✅ WAJIB: biar gak N+1 & sinkron ke accessor
            ->modifyQueryUsing(fn ($query) =>
                $query->withCount('assignments')
            )

            ->columns([

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('nama_program')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn (?string $state): string => match ($state) {
                        'draft'    => 'gray',
                        'berjalan' => 'warning',
                        'selesai'  => 'success',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft'    => 'Draft',
                        'berjalan' => 'Berjalan',
                        'selesai'  => 'Selesai',
                        default    => ucfirst($state ?? '-'),
                    }),

                // ✅ TARGET
                TextColumn::make('target_assignment')
                    ->label('Target')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                // ✅ REALISASI (pakai accessor + withCount)
                TextColumn::make('realisasi_assignment')
                    ->label('Realisasi')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                // ✅ SUDAH LHP
                TextColumn::make('sudah_lhp')
                    ->label('Sudah LHP')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                // ✅ PROGRESS %
                TextColumn::make('progress')
                    ->label('Progress')
                    ->alignCenter()
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 70  => 'info',
                        $state >= 40  => 'warning',
                        default       => 'danger',
                    }),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                TrashedFilter::make(),
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