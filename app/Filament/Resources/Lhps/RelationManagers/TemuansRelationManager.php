<?php

namespace App\Filament\Resources\Lhps\RelationManagers;

use App\Models\Temuan;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TemuansRelationManager extends RelationManager
{
    protected static string $relationship = 'temuans';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2) // 🔥 bikin 2 kolom utama
            ->components([

                // ================= LEFT =================
                Section::make('Uraian Temuan')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Select::make('kode_temuan_id')
                            ->label('Klasifikasi Kode')
                            ->relationship('kodeTemuan', 'kode')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode} - {$record->deskripsi}")
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('kondisi')
                            ->label('Kondisi')
                            ->rows(4)
                            ->placeholder('Jelaskan fakta temuan...')
                            ->required(),

                        Textarea::make('sebab')
                            ->label('Sebab')
                            ->rows(3)
                            ->placeholder('Penyebab terjadinya temuan'),

                        Textarea::make('akibat')
                            ->label('Akibat')
                            ->rows(3)
                            ->placeholder('Dampak dari temuan'),
                    ])
                    ->columnSpan(1),

                // ================= RIGHT =================
                Section::make('Nilai & Status')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Select::make('status_tl')
                            ->label('Status Tindak Lanjut')
                            ->options([
                                Temuan::STATUS_BELUM   => '🔴 Belum TL',
                                Temuan::STATUS_PROSES  => '🟡 Proses',
                                Temuan::STATUS_SELESAI => '🟢 Selesai',
                            ])
                            ->native(false)
                            ->default(Temuan::STATUS_BELUM)
                            ->required(),

                        TextInput::make('nilai_temuan')
                            ->label('Nilai Temuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->inputMode('decimal')
                            ->extraInputAttributes([
                                'class' => 'text-lg font-semibold'
                            ]),

                        Section::make('Rincian Kerugian')
                            ->collapsible()
                            ->collapsed() // 🔥 default tertutup biar clean
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('nilai_kerugian_negara')
                                        ->label('Negara')
                                        ->numeric()
                                        ->prefix('Rp'),

                                    TextInput::make('nilai_kerugian_daerah')
                                        ->label('Daerah')
                                        ->numeric()
                                        ->prefix('Rp'),

                                    TextInput::make('nilai_kerugian_desa')
                                        ->label('Desa')
                                        ->numeric()
                                        ->prefix('Rp'),

                                    TextInput::make('nilai_kerugian_bos_blud')
                                        ->label('BOS / BLUD')
                                        ->numeric()
                                        ->prefix('Rp'),
                                ]),
                            ]),
                    ])
                    ->columnSpan(1),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Temuan')
                ->columns(1)
                ->schema([

                    TextEntry::make('kodeTemuan.kode')
                        ->label('Kode')
                        ->formatStateUsing(fn ($record) =>
                            "{$record->kodeTemuan?->kode} - {$record->kodeTemuan?->deskripsi}"
                        )
                        ->columnSpan(2),

                    TextEntry::make('status_tl')
                        ->label('Status Tindak Lanjut')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            Temuan::STATUS_SELESAI => 'success',
                            Temuan::STATUS_PROSES  => 'warning',
                            default => 'danger',
                        })
                        ->formatStateUsing(fn ($state) => match ($state) {
                            Temuan::STATUS_BELUM   => '🔴 Belum Ditindaklanjuti',
                            Temuan::STATUS_PROSES  => '🟡 Dalam Proses',
                            Temuan::STATUS_SELESAI => '🟢 Selesai',
                        })
                        ->helperText(fn ($state) => match ($state) {
                            Temuan::STATUS_BELUM   => 'Temuan belum mendapatkan tindakan',
                            Temuan::STATUS_PROSES  => 'Sedang dilakukan tindak lanjut',
                            Temuan::STATUS_SELESAI => 'Temuan sudah diselesaikan',
                        }),

                    TextEntry::make('kondisi')->columnSpanFull()->prose(),
                    TextEntry::make('sebab')->columnSpanFull()->prose()->placeholder('Tidak ada'),
                    TextEntry::make('akibat')->columnSpanFull()->prose()->placeholder('Tidak ada'),

                    TextEntry::make('nilai_temuan')
                        ->label('Nilai Temuan')
                        ->money('IDR'),

                    TextEntry::make('total_kerugian')
                        ->label('Total Kerugian')
                        ->money('IDR')
                        ->weight('bold')
                        ->color('danger'),
                ])
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('kondisi')
            ->columns([
                TextColumn::make('kodeTemuan.kode')
                    ->label('Kode')
                    ->sortable()
                    ->description(fn ($record) => $record->kodeTemuan?->deskripsi)
                    ->tooltip(fn ($record) => $record->kodeTemuan?->deskripsi),

                TextColumn::make('kondisi')
                    ->label('Temuan')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('nilai_temuan')
                    ->label('Nilai')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('total_kerugian')
                    ->label('Kerugian')
                    ->money('IDR')
                    ->color('danger')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('status_tl')
                    ->label('Status')
                    ->badge()
                    ->icons([
                        'heroicon-o-x-circle' => Temuan::STATUS_BELUM,
                        'heroicon-o-arrow-path' => Temuan::STATUS_PROSES,
                        'heroicon-o-check-circle' => Temuan::STATUS_SELESAI,
                    ])
                    ->color(fn ($state) => match ($state) {
                        Temuan::STATUS_SELESAI => 'success',
                        Temuan::STATUS_PROSES  => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        Temuan::STATUS_BELUM   => 'Belum TL',
                        Temuan::STATUS_PROSES  => 'Proses',
                        Temuan::STATUS_SELESAI => 'Selesai',
                    })
                    ->description(fn ($state) => match ($state) {
                        Temuan::STATUS_BELUM   => 'Belum ada tindakan',
                        Temuan::STATUS_PROSES  => 'Sedang diproses',
                        Temuan::STATUS_SELESAI => 'Sudah ditindaklanjuti',
                    }),
            ])

            ->filters([
                TrashedFilter::make(),
            ])

            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus'),
                
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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