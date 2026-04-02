<?php

namespace App\Filament\Resources\KodeTemuans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KodeTemuanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Section::make('Kode & Klasifikasi')
                    ->schema([

                        TextInput::make('kode_numerik')
                            ->label('Kode Numerik Resmi')
                            ->placeholder('1.01.01')
                            ->helperText('Format: KEL.SUB_KEL.JENIS sesuai Permenpan 42/2011')
                            ->maxLength(10)
                            ->unique(ignoreRecord: true),

                        TextInput::make('kode')
                            ->label('Kode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(15)
                            ->helperText('Boleh sama dengan kode_numerik atau kode singkat'),

                        Select::make('kel')
                            ->label('Kelompok')
                            ->options([
                                1 => '1 — Ketidakpatuhan Terhadap Peraturan',
                                2 => '2 — Kelemahan Sistem Pengendalian Intern',
                                3 => '3 — Temuan 3E',
                            ])
                            ->required()
                            ->reactive(),

                        Select::make('sub_kel')
                            ->label('Sub Kelompok')
                            ->options(function (callable $get) {
                                return match ((int) $get('kel')) {
                                    1 => [
                                        1 => '01 — Kerugian Negara/Daerah',
                                        2 => '02 — Potensi Kerugian Negara/Daerah',
                                        3 => '03 — Kekurangan Penerimaan',
                                        4 => '04 — Administrasi',
                                        5 => '05 — Indikasi Tindak Pidana',
                                    ],
                                    2 => [
                                        1 => '01 — Kelemahan SPI Akuntansi dan Pelaporan',
                                        2 => '02 — Kelemahan SPI Pelaksanaan Anggaran',
                                        3 => '03 — Kelemahan Struktur Pengendalian Intern',
                                    ],
                                    3 => [
                                        1 => '01 — Ketidakhematan/Pemborosan',
                                        2 => '02 — Ketidakefisienan',
                                        3 => '03 — Ketidakefektifan',
                                    ],
                                    default => [],
                                };
                            })
                            ->required(),

                        TextInput::make('jenis')
                            ->label('Nomor Jenis')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Nomor urut jenis dalam sub kelompok'),

                        TextInput::make('kelompok')
                            ->label('Nama Kelompok')
                            ->maxLength(100),

                        TextInput::make('sub_kelompok')
                            ->label('Nama Sub Kelompok')
                            ->maxLength(150)
                            ->columnSpanFull(),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi Jenis Temuan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Section::make('Alternatif Rekomendasi')
                    ->description('Referensi Lampiran 2.2 — Kode Atribut Rekomendasi')
                    ->schema([

                        Select::make('alternatif_rekom')
                            ->label('Kode Rekomendasi yang Disarankan')
                            ->options(fn () =>
                                \App\Models\KodeRekomendasi::orderBy('kode_numerik')
                                    ->get()
                                    ->mapWithKeys(fn ($r) =>
                                        [$r->kode_numerik => "{$r->kode} — {$r->deskripsi}"]
                                    )
                                    ->toArray()
                            )
                            ->multiple()
                            ->searchable()
                            ->dehydrated(false)
                            ->helperText('Pilih rekomendasi yang relevan untuk jenis temuan ini'),

                    ])
                    ->collapsible(),

            ]);
    }
}
