<?php

namespace App\Filament\Resources\KodeRekomendasis\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KodeRekomendasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Section::make('Identitas Kode')
                ->columns(2)
                ->schema([
                    TextInput::make('kode')
                        ->label('Kode')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder('RK-01'),

                    TextInput::make('kode_numerik')
                        ->label('Kode Numerik')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(14)
                        ->unique(ignoreRecord: true)
                        ->helperText('Nomor resmi PermenPAN 42/2011 (01–14)'),

                    TextInput::make('kategori')
                        ->label('Kategori')
                        ->maxLength(100)
                        ->columnSpanFull(),

                    Textarea::make('deskripsi')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->helperText('Nonaktifkan jika kode tidak lagi digunakan'),
                ]),
            ]);
    }
}
