<?php

namespace App\Filament\Resources\Recommendations\Schemas;

use App\Models\KodeRekomendasi;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RecommendationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::sectionSumber(),
            self::sectionNilai(),
            self::sectionTarget(),
        ]);
    }

    // ── Sections ──────────────────────────────────────────────────────────────

    private static function sectionSumber(): Section
    {
        return Section::make('Informasi Sumber Temuan')
            ->description('Pilih LHP dan Temuan yang masih memerlukan tindak lanjut.')
            ->icon('heroicon-m-information-circle')
            ->columns(2)
            ->schema([

                Select::make('lhp_id')
                ->label('Nomor LHP')
                ->options(fn ($record) => Lhp::query()
                    ->orderByDesc('tanggal_lhp')
                    ->where(function ($q) use ($record) {
                        // ✅ Hanya tampilkan LHP yang masih punya temuan belum selesai
                        $q->whereHas('temuans', fn ($t) =>
                            $t->where('status_tl', '!=', Temuan::STATUS_SELESAI)
                        )
                        // ✅ ATAU LHP dari record yang sedang diedit (agar tidak hilang saat edit)
                        ->orWhere('id', fn ($sub) =>
                            $sub->select('lhp_id')
                                ->from('temuans')
                                ->where('id', $record?->temuan_id)
                                ->limit(1)
                        );
                    })
                    ->pluck('nomor_lhp', 'id')
                )
                ->searchable()
                ->preload()
                ->live()
                ->required()
                ->dehydrated(false)
                ->afterStateHydrated(function (Select $component, $record) {
                    if ($record?->temuan_id) {
                        $lhpId = \App\Models\Temuan::find($record->temuan_id)?->lhp_id;
                        $component->state($lhpId);
                    }
                }),

                Select::make('temuan_id')
                    ->label('Uraian Kondisi Temuan')
                    ->placeholder('--- Pilih Temuan ---')
                    ->options(fn (Get $get, $record) => self::buildTemuanOptions($get('lhp_id'), $record))
                    ->searchable()
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('lhp_id'))
                    ->live()
                    ->columnSpanFull()
                    ->afterStateUpdated(fn ($state, Set $set) => self::handleTemuanChange($state, $set)),

                Select::make('kode_rekomendasi_id')
                    ->label('Kode Rekomendasi')
                    ->options(fn (Get $get) => self::buildKodeOptions($get('temuan_id')))
                    ->helperText(fn (Get $get) => self::getKodeHelperText($get('temuan_id')))
                    ->searchable()
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('temuan_id')),

                Select::make('jenis_rekomendasi')
                    ->label('Jenis Rekomendasi')
                    ->options([
                        Recommendation::JENIS_UANG   => '💰 Keuangan (Rupiah)',
                        Recommendation::JENIS_BARANG => '📦 Aset / Barang',
                        Recommendation::JENIS_ADMIN  => '📄 Administratif',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, Set $set) =>
                        $state !== Recommendation::JENIS_UANG && $set('nilai_rekom', 0)
                    ),

                Textarea::make('uraian_rekom')
                    ->label('Uraian Rekomendasi')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    private static function sectionNilai(): Section
    {
        return Section::make('Nilai Rekomendasi')
            ->visible(fn (Get $get) => $get('jenis_rekomendasi') === Recommendation::JENIS_UANG)
            ->schema([
                Grid::make(3)->schema([

                    TextInput::make('nilai_rekom')
                        ->label('Nominal Rekomendasi')
                        ->prefix('Rp')
                        ->required()
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->inputMode('decimal')
                        ->default(0)
                        ->live(onBlur: true)
                        ->helperText(fn (Get $get, $record) =>
                            self::getPlafonHelperText($get('temuan_id'), $record)
                        )
                        ->rules([
                            fn (Get $get, $record): Closure => self::plafonRule($get('temuan_id'), $record),
                        ]),

                    TextInput::make('nilai_tl_selesai')
                        ->label('Sudah Lunas')
                        ->prefix('Rp')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($record) =>
                            number_format((float) ($record?->nilai_tl_selesai ?? 0), 0, ',', '.')
                        ),

                    TextInput::make('nilai_sisa')
                        ->label('Sisa Kewajiban')
                        ->prefix('Rp')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($record) =>
                            number_format((float) ($record?->nilai_sisa ?? 0), 0, ',', '.')
                        ),
                ]),
            ]);
    }

    private static function sectionTarget(): Section
    {
        return Section::make('Target & Bukti')
            ->columns(2)
            ->schema([

                DatePicker::make('batas_waktu')
                    ->label('Deadline Penyelesaian')
                    ->required()
                    ->native(false),

                Select::make('status')
                    ->options([
                        Recommendation::STATUS_BELUM   => 'Belum Ditindaklanjuti',
                        Recommendation::STATUS_PROSES  => 'Dalam Proses',
                        Recommendation::STATUS_SELESAI => 'Selesai',
                    ])
                    ->default(Recommendation::STATUS_BELUM)
                    ->required(),

                        Repeater::make('attachments')
                            ->relationship('attachments') // <--- WAJIB: Ini yang menghubungkan ke Trait HasAttachments
                            ->schema([
                                FileUpload::make('file_path')
                                    ->label('Upload Dokumen')
                                    ->disk('public')
                                    ->directory('audit/rekomendasi')
                                    // Tambahkan properti ini:
                                    ->imageEditor() 
                                    ->downloadable()
                                    ->openable()
                                    ->reorderable()
                                    ->deletable(true) // Memastikan tombol hapus muncul dan berfungsi
                                    ->storeFileNamesIn('file_name') // Jika ingin sinkron nama file asli
                                    ->required(),

                        TextInput::make('file_name')
                            ->label('Nama Dokumen')
                            ->required(),

                        Hidden::make('uploaded_by')
                            ->default(auth()->id()),
                ])
                // Pastikan item yang dihapus dari repeater benar-benar terhapus dari DB
                ->deletable(true) 
            
            ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function buildTemuanOptions(?int $lhpId, $record): array
    {
        if (! $lhpId) {
            return [];
        }

        return Temuan::query()
            ->where('lhp_id', $lhpId)
            ->where(fn ($q) =>
                $q->where('status_tl', '!=', Temuan::STATUS_SELESAI)
                  ->orWhere('id', $record?->temuan_id)
            )
            ->get()
            ->mapWithKeys(fn ($t) => [
                $t->id => Str::limit($t->kondisi, 100)
                    . ' (Plafon: Rp '
                    . number_format((float) ($t->total_nilai_temuan ?? $t->nilai_temuan ?? 0), 0, ',', '.')
                    . ')',
            ])
            ->toArray();
    }

    private static function handleTemuanChange(?int $state, Set $set): void
    {
        if (! $state) {
            return;
        }

        $set('jenis_rekomendasi', Recommendation::JENIS_UANG);
        $set('uraian_rekom', 'Agar menyetorkan ke Kas Daerah sebesar Rp...');
    }

    private static function buildKodeOptions(?int $temuanId): array
    {
        $semua = KodeRekomendasi::orderBy('kode_numerik')->get();

        if (! $temuanId) {
            return $semua->pluck('deskripsi', 'id')->toArray();
        }

        $temuan     = self::findTemuan($temuanId);
        $alternatif = $temuan->kodeTemuan?->alternatif_rekom ?? [];

        [$saran, $lain] = $semua->partition(fn ($k) => in_array($k->kode_numerik, $alternatif));

        return [
            '⭐ Disarankan' => $saran->mapWithKeys(fn ($k) => [$k->id => "{$k->kode} - {$k->deskripsi}"])->toArray(),
            'Lainnya'       => $lain->mapWithKeys(fn ($k) => [$k->id => "{$k->kode} - {$k->deskripsi}"])->toArray(),
        ];
    }

    private static function getKodeHelperText(?int $temuanId): string
    {
        if (! $temuanId) {
            return 'Pilih temuan dulu.';
        }

        $temuan = self::findTemuan($temuanId);
        $labels = KodeRekomendasi::whereIn(
            'kode_numerik',
            $temuan->kodeTemuan?->alternatif_rekom ?? []
        )->pluck('kode')->join(', ');

        return $labels ? "💡 Saran: **{$labels}**" : 'Pilih kode yang sesuai.';
    }

    private static function getPlafonHelperText(?int $temuanId, $record): ?string
    {
        if (! $temuanId) {
            return null;
        }

        $temuan = self::findTemuan($temuanId);
        $plafon = self::getPlafon($temuan);

        if ($plafon <= 0) {
            return '💡 Temuan ini tercatat Rp 0 (Administratif). Bebas input nominal.';
        }

        $terpakai = self::getNilaiTerpakai($temuanId, $record?->id);
        $sisa     = $plafon - $terpakai;

        return 'Total Plafon: Rp ' . number_format($plafon, 0, ',', '.')
            . ' | Sisa Tersedia: Rp ' . number_format($sisa, 0, ',', '.');
    }

    private static function plafonRule(?int $temuanId, $record): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($temuanId, $record) {
            if (! $temuanId) {
                return;
            }

            $temuan = self::findTemuan($temuanId);
            $plafon = self::getPlafon($temuan);

            if ($plafon <= 0) {
                return;
            }

            $input    = max(0.0, (float) preg_replace('/[^0-9.]/', '', (string) $value));
            $terpakai = self::getNilaiTerpakai($temuanId, $record?->id);
            $maksimal = $plafon - $terpakai;

            if ($input > $maksimal) {
                $fail('Nilai melebihi sisa plafon. Maksimal: Rp ' . number_format($maksimal, 0, ',', '.'));
            }
        };
    }

    // ── Low-level utilities ───────────────────────────────────────────────────

    private static function findTemuan(int $id): Temuan
    {
        static $cache = [];

        return $cache[$id] ??= Temuan::with('kodeTemuan')->findOrFail($id);
    }

    private static function getPlafon(Temuan $temuan): float
{
    return (float) (
        $temuan->nilai_temuan
        ?? $temuan->total_nilai_temuan
        ?? $temuan->nilai_kerugian_negara
        ?? 0
    );
}

    private static function getNilaiTerpakai(int $temuanId, ?int $excludeId): float
    {
        return (float) Recommendation::where('temuan_id', $temuanId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereNull('deleted_at')
            ->sum('nilai_rekom');
    }
}