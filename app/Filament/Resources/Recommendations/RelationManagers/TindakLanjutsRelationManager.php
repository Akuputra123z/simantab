<?php

namespace App\Filament\Resources\Recommendations\RelationManagers;

use App\Models\TindakLanjut;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TindakLanjutsRelationManager extends RelationManager
{
    protected static string $relationship = 'tindakLanjuts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('jenis_penyelesaian')
                    ->default(null),
                Toggle::make('is_cicilan')
                    ->required(),
                TextInput::make('jumlah_cicilan_rencana')
                    ->numeric()
                    ->default(null),
                DatePicker::make('tanggal_mulai_cicilan'),
                DatePicker::make('tanggal_jatuh_tempo'),
                TextInput::make('nilai_per_cicilan_rencana')
                    ->numeric()
                    ->default(null),
                TextInput::make('jumlah_cicilan_realisasi')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_terbayar')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('sisa_belum_bayar')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('catatan_tl')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('hambatan')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('status_verifikasi')
                    ->required()
                    ->default('menunggu_verifikasi'),
                TextInput::make('diverifikasi_oleh')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('diverifikasi_pada'),
                Textarea::make('catatan_verifikasi')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric()
                    ->default(null),
                TextInput::make('updated_by')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('jenis_penyelesaian')
                    ->placeholder('-'),
                IconEntry::make('is_cicilan')
                    ->boolean(),
                TextEntry::make('jumlah_cicilan_rencana')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('tanggal_mulai_cicilan')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('tanggal_jatuh_tempo')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('nilai_per_cicilan_rencana')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('jumlah_cicilan_realisasi')
                    ->numeric(),
                TextEntry::make('total_terbayar')
                    ->numeric(),
                TextEntry::make('sisa_belum_bayar')
                    ->numeric(),
                TextEntry::make('catatan_tl')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('hambatan')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status_verifikasi'),
                TextEntry::make('diverifikasi_oleh')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('diverifikasi_pada')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('catatan_verifikasi')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('updated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (TindakLanjut $record): bool => $record->trashed()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('jenis_penyelesaian')
            ->columns([
                TextColumn::make('jenis_penyelesaian')
                    ->searchable(),
                IconColumn::make('is_cicilan')
                    ->boolean(),
                TextColumn::make('jumlah_cicilan_rencana')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tanggal_mulai_cicilan')
                    ->date()
                    ->sortable(),
                TextColumn::make('tanggal_jatuh_tempo')
                    ->date()
                    ->sortable(),
                TextColumn::make('nilai_per_cicilan_rencana')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('jumlah_cicilan_realisasi')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_terbayar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sisa_belum_bayar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_verifikasi')
                    ->searchable(),
                TextColumn::make('diverifikasi_oleh')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('diverifikasi_pada')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
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
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
