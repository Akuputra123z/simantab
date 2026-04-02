<?php

namespace App\Filament\Resources\AuditAssignments\Schemas;

use App\Models\UnitDiperiksa;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid; // Note: Ensure you use Forms\Components, not Schemas\Components
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuditAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Audit')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('audit_program_id')
                                ->relationship('auditProgram', 'nama_program')
                                ->required()
                                ->searchable()
                                ->preload(),

                            TextInput::make('nomor_surat')
                                ->label('Nomor Surat Tugas')
                                ->required()
                                ->placeholder('700/001/INSPEKTORAT/2026')
                                ->maxLength(100)
                                ->columnSpanFull(),

                            Select::make('filter_kategori')
                                ->label('Kategori Unit')
                                ->searchable()
                                ->options(fn () => Cache::remember(
                                    'kategori_options',
                                    now()->addHours(6),
                                    fn () => UnitDiperiksa::query()
                                        ->select('kategori')
                                        ->distinct()
                                        ->orderBy('kategori')
                                        ->pluck('kategori', 'kategori')
                                        ->toArray()
                                ))
                                ->afterStateHydrated(function ($state, callable $set, $record) {
                                    if ($record?->unitDiperiksa) {
                                        $set('filter_kategori', $record->unitDiperiksa->kategori);
                                    }
                                })
                                ->live(debounce: 300)
                                ->afterStateUpdated(fn (callable $set) => [
                                    $set('filter_kecamatan', null),
                                    $set('unit_diperiksa_id', null),
                                ])
                                ->dehydrated(false),

                            Select::make('filter_kecamatan')
                                ->label('Kecamatan')
                                ->searchable()
                                ->disabled(fn (callable $get) => !$get('filter_kategori'))
                                ->options(function (callable $get) {
                                    $kategori = $get('filter_kategori');
                                    if (!$kategori) return [];

                                    return Cache::remember(
                                        "kecamatan_options_{$kategori}",
                                        now()->addHours(6),
                                        fn () => UnitDiperiksa::query()
                                            ->where('kategori', $kategori)
                                            ->select('nama_kecamatan')
                                            ->distinct()
                                            ->orderBy('nama_kecamatan')
                                            ->pluck('nama_kecamatan', 'nama_kecamatan')
                                            ->toArray()
                                    );
                                })
                                ->afterStateHydrated(function ($state, callable $set, $record) {
                                    if ($record?->unitDiperiksa) {
                                        $set('filter_kecamatan', $record->unitDiperiksa->nama_kecamatan);
                                    }
                                })
                                ->live(debounce: 300)
                                ->afterStateUpdated(fn (callable $set) => $set('unit_diperiksa_id', null))
                                ->dehydrated(false),

                            Select::make('unit_diperiksa_id')
                                ->label('Nama Unit')
                                ->relationship(
                                    name: 'unitDiperiksa',
                                    titleAttribute: 'nama_unit',
                                    modifyQueryUsing: function ($query, callable $get) {
                                        $query
                                            ->when($get('filter_kategori'), fn ($q) =>
                                                $q->where('kategori', $get('filter_kategori'))
                                            )
                                            ->when($get('filter_kecamatan'), fn ($q) =>
                                                $q->where('nama_kecamatan', $get('filter_kecamatan'))
                                            );
                                    }
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->disabled(fn (callable $get) => !$get('filter_kategori')),

                            TextInput::make('nama_tim')
                                ->label('Nama Tim Audit')
                                ->required()
                                ->maxLength(255),
                        ]), // Fixed: Added ']' here to close the Grid schema array
                    ]), // Fixed: Added ']' here to close the Section schema array

                Section::make('Jadwal & Personel')
                    ->schema([
                        Grid::make(3)->schema([
                            DatePicker::make('tanggal_mulai')
                                ->required()
                                ->native(false),

                            DatePicker::make('tanggal_selesai')
                                ->required()
                                ->native(false)
                                ->afterOrEqual('tanggal_mulai'),

                            Select::make('status')
                                ->options([
                                    'draft' => 'Draft',
                                    'berjalan' => 'Berjalan',
                                    'selesai' => 'Selesai',
                                ])
                                ->required()
                                ->default('draft'),
                        ]),

                        Select::make('ketua_tim_id')
                            ->label('Ketua Tim')
                            ->relationship('ketuaTim', 'name')
                            ->default(fn () => auth()->id())
                            ->dehydrated(true) // ✅ WAJIB supaya ikut ke DB
                            ->required()
                            ->disabled(fn () => !auth()->user()->hasRole('super_admin')),
                        Select::make('members')
                            ->label('Anggota Tim')
                            ->relationship(
                                name: 'members',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->orderBy('name')
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),


                    Section::make('Dokumen')
                    ->schema([

                        Repeater::make('attachments')
                                ->relationship('attachments')
                                ->schema([
                                    FileUpload::make('file_path')
                                        ->label('Upload Dokumen')
                                        ->disk('public')
                                        ->directory('audit-attachments')
                                        ->downloadable()
                                        ->openable()
                                        ->reorderable()
                                        ->deletable(true)
                                        ->storeFileNamesIn('file_name')
                                       
                                ]),
                    ]),

                    
            ]);
    }
}