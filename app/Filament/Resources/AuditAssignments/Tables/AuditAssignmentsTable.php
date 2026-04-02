<?php

namespace App\Filament\Resources\AuditAssignments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AuditAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
             TextColumn::make('auditProgram.nama_program')
                    ->label('Program Audit')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('unitDiperiksa.nama_unit')
                    ->label('Unit Terperiksa')
                    ->description(fn ($record) => "Kec. {$record->unitDiperiksa->nama_kecamatan} ({$record->unitDiperiksa->kategori})")
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ketuaTim.name')
                    ->label('Ketua Tim')
                    ->icon('heroicon-m-user')
                    ->sortable(),

                TextColumn::make('nama_tim')
                    ->label('Tim')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('members_count')
                    ->label('Anggota')
                    ->counts('members')
                    ->suffix(' Orang')
                    ->alignCenter(),

                TextColumn::make('tanggal_mulai')
                    ->label('Jadwal')
                    ->description(fn ($record) => "s/d " . $record->tanggal_selesai?->format('d/m/Y'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'berjalan' => 'warning',
                        'selesai' => 'success',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
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
