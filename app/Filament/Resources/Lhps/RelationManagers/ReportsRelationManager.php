<?php

namespace App\Filament\Resources\Lhps\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('jenis_laporan')
                    ->required(),
                TextInput::make('file_path')
                    ->default(null),
                Textarea::make('snapshot_data')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('generated_at'),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('jenis_laporan'),
                TextEntry::make('file_path')
                    ->placeholder('-'),
                TextEntry::make('snapshot_data')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('generated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('jenis_laporan')
            ->columns([
                TextColumn::make('jenis_laporan')
                    ->label('Laporan')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => "ID: " . basename($record->file_path ?? 'No File')),

                TextColumn::make('generated_at')
                    ->label('Waktu Pembuatan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('file_path')
                    ->label('Status File')
                    ->formatStateUsing(fn ($state) => $state ? 'Tersedia' : 'Kosong')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                TextColumn::make('created_at')
                    ->label('Dibuat System')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
