<?php

namespace App\Filament\Resources\AuditPrograms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program Audit')
                    ->columns(2)
                    ->schema([

                        TextInput::make('nama_program')
                            ->label('Nama Program')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),

                        Select::make('tahun')
                            ->label('Tahun Program')
                            ->options(
                                collect(range(now()->year - 5, now()->year + 2))
                                    ->mapWithKeys(fn ($year) => [$year => $year])
                            )
                            ->default(now()->year)
                            ->required()
                            ->native(false),

                        TextInput::make('target_assignment')
                            ->label('Target Assignment')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->minValue(0)
                            ->suffix('Audit'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'berjalan' => 'Berjalan',
                                'selesai' => 'Selesai',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }
}