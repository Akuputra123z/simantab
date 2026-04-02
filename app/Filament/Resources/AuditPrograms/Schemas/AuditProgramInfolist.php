<?php

namespace App\Filament\Resources\AuditPrograms\Schemas;

use App\Models\AuditProgram;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditProgramInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // 🔥 SECTION UTAMA
                Section::make('Informasi Program')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('nama_program')
                            ->label('Nama Program')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('tahun')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'draft'    => 'gray',
                                'berjalan' => 'warning',
                                'selesai'  => 'success',
                                default    => 'gray',
                            }),

                        TextEntry::make('target_assignment')
                            ->label('Target')
                            ->badge()
                            ->color('info'),

                    ]),

                // 🔥 SECTION KPI (INI YANG BIKIN KELIHATAN PRO)
                Section::make('Statistik')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('realisasi_assignment')
                            ->label('Realisasi')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('sudah_lhp')
                            ->label('Sudah LHP')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('sisa_target')
                            ->label('Sisa Target')
                            ->badge()
                            ->color('danger'),

                        TextEntry::make('progress')
                            ->label('Progress')
                            ->suffix('%')
                            ->badge()
                            ->color(fn ($state) => match (true) {
                                $state >= 80 => 'success',
                                $state >= 50 => 'warning',
                                default      => 'danger',
                            })
                            ->formatStateUsing(fn ($state) => number_format($state, 1)),

                    ]),

                // 🔥 SECTION METADATA
                Section::make('Metadata')
                    ->collapsible()
                    ->columns(2)
                    ->schema([

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i')
                            ->since(),

                        TextEntry::make('updated_at')
                            ->label('Diupdate')
                            ->dateTime('d M Y H:i')
                            ->since(),

                        TextEntry::make('deleted_at')
                            ->label('Dihapus')
                            ->dateTime('d M Y H:i')
                            ->visible(fn (AuditProgram $record): bool => $record->trashed()),

                    ]),
            ]);
    }
}