<?php

namespace App\Filament\Resources\TindakLanjuts\Schemas;

use App\Models\Lhp;
use App\Models\Recommendation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class TindakLanjutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::sectionSumberTemuan(),
            self::sectionPenyelesaian(),
            self::sectionRencanaCicilan(),
            self::sectionUploadBukti(),
            self::sectionVerifikasi(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  SECTION 1: SUMBER TEMUAN
    // ──────────────────────────────────────────────────────────────

  private static function sectionSumberTemuan(): Section
{
    return Section::make('1. Pilih Sumber Temuan')
        ->columns(2)
        ->schema([

            Select::make('lhp_id')
                ->label('Nomor LHP')
                ->options(fn () => Lhp::pluck('nomor_lhp', 'id'))
                ->searchable()
                ->preload()
                ->live()
                ->required()
                ->dehydrated(false)
                ->afterStateUpdated(
                    fn (Set $set, $context) =>
                        $context !== 'edit' ? $set('recommendation_id', null) : null
                )
                ->afterStateHydrated(function (Set $set, $record): void {
                    if ($record?->recommendation?->temuan?->lhp_id) {
                        $set('lhp_id', $record->recommendation->temuan->lhp_id);
                    }
                }),

            Select::make('recommendation_id')
                ->label('Rekomendasi')
                ->options(function (Get $get) {
                        $user  = auth()->user();
                        $lhpId = $get('lhp_id');

                        if (!$lhpId) return [];

                        return \App\Models\Recommendation::query()
                            ->where('status', '!=', 'selesai') // 🔥 FILTER INI
                            ->whereHas('temuan.lhp', function ($q) use ($user, $lhpId) {
                                $q->where('id', $lhpId)
                                ->when(
                                    !$user->hasRole(['super_admin', 'kepala_inspektorat', 'staff']),
                                    function ($q2) use ($user) {
                                        $q2->whereHas('auditAssignment', function ($q3) use ($user) {
                                            $q3->where('ketua_tim_id', $user->id)
                                                ->orWhereHas('members', fn ($q4) =>
                                                    $q4->where('user_id', $user->id)
                                                );
                                        });
                                    }
                                );
                            })
                        ->orderByDesc('id')
                        ->limit(100)
                        ->pluck('uraian_rekom', 'id')
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->columnSpanFull()
                ->disabled(fn (Get $get) => ! $get('lhp_id'))

                // ✅ CREATE
                ->afterStateUpdated(function (Set $set, $state, $context): void {
                    if ($context === 'edit' || ! $state) return;

                    $rekom = \App\Models\Recommendation::find($state);
                    if (!$rekom) return;

                    $nilai = self::parseMoney($rekom->nilai_rekom);

                    $set('nilai_rekom_max', $nilai);
                    $set('nilai_tl_raw', $nilai);

                    self::syncAll($set, $nilai, 0);
                })

                // ✅ EDIT
                ->afterStateHydrated(function (Set $set, $state, $record): void {
                    if (! $state || ! $record) return;

                    $rekom = \App\Models\Recommendation::find($state);
                    if (! $rekom) return;

                    $nilaiRekom = self::parseMoney($rekom->nilai_rekom);
                    $nilaiTl    = self::parseMoney($record->nilai_tindak_lanjut);

                    $set('nilai_rekom_max', $nilaiRekom);
                    $set('nilai_tl_raw', $nilaiTl);

                    self::syncAll($set, $nilaiTl, $nilaiRekom);
                }),

            Hidden::make('nilai_rekom_max')->default(0),
            Hidden::make('nilai_tl_raw')->default(0),
        ]);
}
    // ──────────────────────────────────────────────────────────────
    //  SECTION 2: PENYELESAIAN & RINGKASAN
    // ──────────────────────────────────────────────────────────────

    private static function sectionPenyelesaian(): Section
    {
        return Section::make('2. Penyelesaian & Ringkasan')
            ->description('Tentukan metode dan lihat ringkasan otomatis')
            ->icon('heroicon-o-adjustments-horizontal')
            ->schema([
                Grid::make(2)->schema([

                    Select::make('jenis_penyelesaian')
                        ->label('Jenis Penyelesaian')
                        ->options([
                            'setor_kas'              => 'Setor Kas',
                            'pengembalian_barang'    => 'Pengembalian Barang',
                            'perbaikan_administrasi' => 'Perbaikan Administrasi',
                            'cicilan'                => 'Cicilan',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, $state): void {
                            $nilaiRekom = self::getNilaiRekom($get);

                            if ($state === 'cicilan') {
                                $set('nilai_tindak_lanjut', self::toMask(0));
                                $set('nilai_tl_raw',        0);
                                $set('total_terbayar',      self::toMask(0));
                                $set('sisa_belum_bayar',    self::toMask($nilaiRekom));
                            } else {
                                $set('nilai_tindak_lanjut', self::toMask($nilaiRekom));
                                $set('nilai_tl_raw',        $nilaiRekom);
                                $set('total_terbayar',      self::toMask(0));
                                $set('sisa_belum_bayar',    self::toMask($nilaiRekom));
                            }
                        }),

                    TextInput::make('nilai_tindak_lanjut')
    ->label('Nilai Tindak Lanjut')
    ->prefix('Rp')
    ->mask(RawJs::make(<<<'JS'
        $money($input, ',', '.')
    JS))
    ->required()
    ->live(onBlur: true)
    ->readOnly(fn (Get $get): bool => $get('jenis_penyelesaian') === 'cicilan')
    ->helperText(fn (Get $get): ?string => $get('jenis_penyelesaian') === 'cicilan'
        ? 'Dihitung otomatis dari rencana cicilan'
        : null
    )
    ->dehydrateStateUsing(fn ($state) => number_format(self::parseMoney($state), 2, '.', ''))
    ->afterStateUpdated(function (Get $get, Set $set, $state): void {
        $raw = self::parseMoney($state);
        $set('nilai_tl_raw', $raw);

        if ($get('jenis_penyelesaian') !== 'cicilan') {
            self::syncAll($set, $raw, self::getNilaiRekom($get));
        }
    }),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('total_terbayar')
                        ->label('💰 Terbayar')
                        ->prefix('Rp')
                        ->readOnly()
                        ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                        ->dehydrated(true)
                        // ✅ Simpan "100000.00" ke DB — tidak pernah string mask
                        ->dehydrateStateUsing(fn ($state) => number_format(self::parseMoney($state), 2, '.', '')),

                    TextInput::make('sisa_belum_bayar')
                        ->label('📉 Sisa')
                        ->prefix('Rp')
                        ->readOnly()
                        ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                        ->dehydrated(true)
                        // ✅ Simpan "100000.00" ke DB — tidak pernah string mask
                        ->dehydrateStateUsing(fn ($state) => number_format(self::parseMoney($state), 2, '.', '')),
                ]),
            ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  SECTION 3: RENCANA CICILAN
    // ──────────────────────────────────────────────────────────────

    private static function sectionRencanaCicilan(): Section
    {
        return Section::make('3. Rencana Cicilan')
            ->visible(fn (Get $get): bool => $get('jenis_penyelesaian') === 'cicilan')
            ->columns(2)
            ->schema([
                TextInput::make('jumlah_cicilan_rencana')
                    ->label('Jumlah Cicilan')
                    ->numeric()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungNilaiCicilan($get, $set)),

                TextInput::make('nilai_per_cicilan_rencana')
                    ->label('Per Cicilan')
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                    ->live(debounce: 500)

                    // 🔥 FIX UTAMA
                    ->formatStateUsing(fn ($state) => self::toMask(self::parseMoney($state)))

                    ->dehydrateStateUsing(fn ($state) => number_format(self::parseMoney($state), 2, '.', ''))

                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        // 🔥 AMANKAN dari double parse
                        $raw = self::parseMoney($state);

                        self::hitungTotalDariPerCicilan($get, $set, $raw);
                    }),
                DatePicker::make('tanggal_mulai_cicilan')
                    ->label('Tanggal Mulai')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->formatStateUsing(fn ($state) => self::safeDateOrNull($state)),

                DatePicker::make('tanggal_jatuh_tempo')
                    ->label('Tanggal Jatuh Tempo')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->formatStateUsing(fn ($state) => self::safeDateOrNull($state)),
            ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  SECTION 4: UPLOAD BUKTI
    // ──────────────────────────────────────────────────────────────

    private static function sectionUploadBukti(): Section
    {
        return Section::make('4. Upload Bukti')
            ->schema([
                Repeater::make('attachments')
                    ->relationship('attachments')
                    ->collapsed()
                    ->schema([
                        FileUpload::make('file_path')
                            ->required()
                            ->disk('public')
                            ->directory('tindak-lanjut')
                            ->visibility('public'),

                        TextInput::make('file_name')
                            ->label('Nama File'),

                        Hidden::make('uploaded_by')->default(auth()->id()),
                        Hidden::make('jenis_bukti')->default('tindak_lanjut'),
                    ]),
            ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  SECTION 5: VERIFIKASI
    // ──────────────────────────────────────────────────────────────

    private static function sectionVerifikasi(): Section
    {
        return Section::make('5. Verifikasi')
            ->columns(2)
            ->schema([
                Select::make('status_verifikasi')
    ->label('Status Verifikasi')
    ->options([
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'berjalan'            => 'Berjalan',
        'lunas'               => 'Lunas',
        'ditolak'             => 'Ditolak',
    ])
    ->required()
    ->live()
    ->afterStateUpdated(function (Get $get, Set $set, $state, $context): void {

        if ($context === 'edit') {
            return;
        }

        if (in_array($state, ['menunggu_verifikasi', 'ditolak'], true)) {
            $set('total_terbayar', self::toMask(0));
            $set('sisa_belum_bayar', self::toMask(self::getNilaiRekom($get)));
            return;
        }

        if ($state === 'lunas') {
            self::syncAll(
                $set,
                self::getNilaiRekom($get),
                self::getNilaiRekom($get)
            );
        }
    }),

                Select::make('diverifikasi_oleh')
                    ->label('Diverifikasi Oleh')
                    ->relationship('verifikator', 'name')
                    ->searchable()
                    ->preload(),

                DatePicker::make('diverifikasi_pada')
                    ->label('Tanggal Verifikasi')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->formatStateUsing(fn ($state) => self::safeDateOrNull($state)),

                Textarea::make('catatan_tl')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  HELPER FUNCTIONS
    // ──────────────────────────────────────────────────────────────

    private static function getRekomOptions(Get $get, $record): Collection
    {
        $user  = auth()->user();
        $lhpId = $get('lhp_id') ?? $record?->recommendation?->temuan?->lhp_id;

        if (! $lhpId) {
            return collect();
        }

        return Recommendation::query()
            ->whereHas('temuan', function ($q) use ($lhpId, $user) {
                $q->where('lhp_id', $lhpId);

                if (! $user->hasRole('super_admin')) {
                    $q->whereHas('lhp.auditAssignment', function ($sub) use ($user) {
                        $sub->where('ketua_tim_id', $user->id)
                            ->orWhereHas('members', function ($q2) use ($user) {
                                $q2->where('audit_assignment_members.user_id', $user->id);
                            });
                    });
                }
            })
            ->where(fn ($q) => $q
                ->whereDoesntHave('tindakLanjuts')
                ->orWhere('id', $record?->recommendation_id)
            )
            ->with(['temuan.kodeTemuan'])
            ->get()
            ->mapWithKeys(fn (Recommendation $r) => [
                $r->id => sprintf(
                    '[%s | R-%s] Rp %s — Sisa: Rp %s',
                    $r->temuan?->kodeTemuan?->kode ?? '-',
                    str_pad($r->id, 2, '0', STR_PAD_LEFT),
                    number_format(self::parseMoney($r->nilai_rekom), 0, ',', '.'),
                    // ✅ Tampilkan sisa kewajiban dari Recommendation langsung
                    number_format(self::parseMoney($r->nilai_sisa), 0, ',', '.')
                ),
            ]);
    }

    /**
     * Update total_terbayar & sisa_belum_bayar.
     * nilaiRekom = nilai_rekom_max (dari Recommendation::nilai_rekom).
     */
    private static function updateProgress(float $nilaiRealisasi, Get $get, Set $set): void
    {
        $nilaiRekom = self::getNilaiRekom($get);

        $set('total_terbayar',   self::toMask($nilaiRealisasi));
        $set('sisa_belum_bayar', self::toMask(max(0.0, $nilaiRekom - $nilaiRealisasi)));
    }

    /**
     * Ambil nilai_rekom_max (cache dari Recommendation::nilai_rekom).
     */
    private static function getNilaiRekom(Get $get): float
    {
        return self::parseMoney($get('nilai_rekom_max') ?? 0);
    }

    /**
     * Ambil nilai_tl_raw. Fallback ke nilai_rekom_max jika belum diset.
     */
    private static function getNilaiTlRaw(Get $get): float
    {
        $raw = self::parseMoney($get('nilai_tl_raw') ?? 0);
        return $raw > 0 ? $raw : self::getNilaiRekom($get);
    }

   private static function hitungNilaiCicilan(Get $get, Set $set): void
{
    $jumlah     = (int) $get('jumlah_cicilan_rencana');
    $nilaiRekom = self::getNilaiRekom($get);

    if ($jumlah <= 0) {
        return;
    }

    $perCicilan = round($nilaiRekom / $jumlah, 2);

    // 🔥 SET RAW DULU, BARU MASK
    $set('nilai_per_cicilan_rencana', self::toMask($perCicilan));

    self::syncAll($set, 0, $nilaiRekom);

    $set('nilai_tl_raw', $nilaiRekom);
}

    private static function hitungTotalDariPerCicilan(Get $get, Set $set, $perCicilanRaw = null): void
{
    $jumlah = (int) $get('jumlah_cicilan_rencana');

    // 🔥 AMAN: pakai raw dari parameter
    $perCicilan = $perCicilanRaw ?? self::parseMoney($get('nilai_per_cicilan_rencana'));

    if ($jumlah <= 0) {
        return;
    }

    $total = $perCicilan * $jumlah;

    $set('nilai_tl_raw', $total);
    $set('nilai_tindak_lanjut', self::toMask($total));

    // 🔥 WAJIB: jangan parse lagi
    self::syncAll($set, $total, self::getNilaiRekom($get));
}

  
    private static function parseMoney(mixed $value): float
{
    if ($value === null || $value === '' || is_array($value) || is_object($value)) {
        return 0.0;
    }

    if (is_numeric($value)) {
        return (float) $value;
    }

    $str = trim((string) $value);

    if ($str === '' || $str === '-') {
        return 0.0;
    }

    // ✅ DETEK FORMAT INDONESIA
    if (str_contains($str, ',') && str_contains($str, '.')) {
        // format: 100.000,00
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);
    } 
    // ✅ FORMAT: 100,000.00 (US)
    elseif (str_contains($str, ',')) {
        $str = str_replace(',', '', $str);
    }

    return is_numeric($str) ? (float) $str : 0.0;
}

    /**
     * Format float ke string mask '$money($input, \',\', \'.\')'.
     *
     * WAJIB dipakai setiap kali $set() ke field ber-mask dari PHP.
     *   100000.0 → "100.000,00"
     */
    private static function toMask(float|int $value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }

    /**
     * Konversi nilai ke string "100000.00" untuk disimpan ke kolom decimal DB.
     * Dipakai di dehydrateStateUsing.
     */
    private static function toDecimal(mixed $value): string
    {
        return number_format(self::parseMoney($value), 2, '.', '');
    }

    /**
     * Konversi string tanggal ke Y-m-d, atau null jika tidak valid.
     */
    private static function safeDateOrNull(mixed $value): ?string
    {
        if (blank($value) || $value === '-' || ! is_string($value)) {
            return null;
        }

        try {
            $carbon = \Carbon\Carbon::parse($value);
            if ($carbon->year < 1900 || $carbon->year > 2100) {
                return null;
            }
            return $carbon->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private static function syncAll(Set $set, float $terbayar, float $nilaiRekom): void
{
    $set('nilai_tindak_lanjut', self::toMask($terbayar));
    $set('total_terbayar', self::toMask($terbayar));
    $set('sisa_belum_bayar', self::toMask(max(0, $nilaiRekom - $terbayar)));
}
}