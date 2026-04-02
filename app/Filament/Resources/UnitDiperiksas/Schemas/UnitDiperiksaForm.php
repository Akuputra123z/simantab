<?php

namespace App\Filament\Resources\UnitDiperiksas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UnitDiperiksaForm
{
    // Placeholder nama_unit per kategori untuk UX hint
    private const PLACEHOLDER = [
        'SKPD'      => 'Contoh: Dinas Pendidikan Kab. Rembang',
        'Sekolah'   => 'Contoh: SMPN 1 Sulang',
        'Puskesmas' => 'Contoh: Puskesmas Sulang',
        'Desa'      => 'Contoh: Desa Pragu',
        'BLUD'      => 'Contoh: RSUD dr. R. Soetrasno',
        'default'   => 'Masukkan nama unit diperiksa',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Identitas Unit ────────────────────────────────────────────
                Section::make('Identitas Unit')
                    ->columns(2)
                    ->schema([

                        Select::make('kategori')
                            ->label('Kategori')
                            ->options([
                                'SKPD'      => 'SKPD',
                                'Sekolah'   => 'Sekolah',
                                'Puskesmas' => 'Puskesmas',
                                'Desa'      => 'Desa',
                                'BLUD'      => 'BLUD',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('nama_unit', null)),

                        TextInput::make('nama_kecamatan')
                            ->label('Kecamatan')
                            ->maxLength(100)
                            ->placeholder('Contoh: Sulang')
                            ->helperText(fn (Get $get): ?string =>
                                in_array($get('kategori'), ['SKPD', 'BLUD'])
                                    ? 'Opsional untuk kategori ini.'
                                    : null
                            ),

                        TextInput::make('nama_unit')
                            ->label('Nama Unit')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull()
                            ->placeholder(fn (Get $get): string =>
                                self::PLACEHOLDER[$get('kategori') ?? 'default']
                                    ?? self::PLACEHOLDER['default']
                            )
                            ->helperText(fn (Get $get): ?string => match ($get('kategori')) {
                                'Sekolah'   => 'Gunakan nama resmi sesuai Dapodik.',
                                'Puskesmas' => 'Gunakan nama resmi sesuai Permenkes.',
                                'Desa'      => 'Gunakan nama resmi sesuai data Kemendagri.',
                                default     => null,
                            }),
                    ]),

                // ── Kontak & Keterangan ───────────────────────────────────────
                Section::make('Kontak & Keterangan')
                    ->description('Opsional — untuk keperluan surat menyurat dan referensi.')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([

                        TextInput::make('alamat')
                            ->label('Alamat')
                            ->maxLength(500)
                            ->placeholder('Jl. Raya Sulang No. 12, Rembang')
                            ->columnSpanFull(),

                        TextInput::make('telepon')
                            ->label('Telepon / WA')
                            ->maxLength(20)
                            ->placeholder('0812-3456-7890')
                            ->tel(),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->maxLength(500)
                            ->rows(2)
                            ->placeholder('Catatan tambahan jika diperlukan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}