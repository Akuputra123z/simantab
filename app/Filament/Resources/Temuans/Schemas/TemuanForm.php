<?php

namespace App\Filament\Resources\Temuans\Schemas;

use App\Models\KodeTemuan;
use App\Models\Temuan;
use App\Models\Lhp;
use Filament\Support\RawJs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;

use Filament\Schemas\Schema;

class TemuanForm
{
    private const MAX_NILAI = 999_000_000_000;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── STEP 1: Informasi Temuan ──────────────────────────────
                Section::make('Informasi Temuan')
                    ->columns(2)
                    ->schema([

                    Select::make('lhp_id')
    ->label('LHP')
    ->relationship(
        name: 'lhp',
        titleAttribute: 'nomor_lhp',
        modifyQueryUsing: function (Builder $query) {
            $user = Auth::user();

            if ($user->hasRole('super_admin')) {
                return $query;
            }

            return $query->whereHas('auditAssignment', function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('ketua_tim_id', $user->id)
                        ->orWhereHas('members', function ($q2) use ($user) {
                            $q2->where('user_id', $user->id);
                        });
                });
            });
        }
    )
    ->searchable()
    ->preload()
    ->required()
    ->columnSpanFull(),

                        Select::make('kode_temuan_id')
                            ->label('Kode Temuan')
                            ->relationship('kodeTemuan', 'kode')
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                "[{$record->kode_numerik}]  — {$record->deskripsi}"
                            )
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                            ->nullable()
                            ->exists('kode_temuans', 'id')
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state): void {
                                if (! $state) {
                                    return;
                                }

                                $kode = KodeTemuan::find($state);
                                if (! $kode) {
                                    return;
                                }

                                $isNonKeuangan = in_array($kode->kel, [2, 3])
                                    || ($kode->kel === 1 && $kode->sub_kel === 4);

                                if ($isNonKeuangan) {
                                    $set('nilai_kerugian_negara',   0);
                                    $set('nilai_kerugian_daerah',   0);
                                    $set('nilai_kerugian_desa',     0);
                                    $set('nilai_kerugian_bos_blud', 0);
                                    $set('nilai_temuan',            0);
                                }
                            }),

                        Select::make('status_tl')
                            ->label('Status Tindak Lanjut')
                            ->options([
                                Temuan::STATUS_BELUM   => 'Belum Ditindaklanjuti',
                                Temuan::STATUS_PROSES  => 'Dalam Proses',
                                Temuan::STATUS_SELESAI => 'Selesai',
                            ])
                            ->default(Temuan::STATUS_BELUM)
                            ->required()
                            ->in([
                                Temuan::STATUS_BELUM,
                                Temuan::STATUS_PROSES,
                                Temuan::STATUS_SELESAI,
                            ])
                            ->native(false)
                            ->helperText('Diperbarui otomatis oleh sistem.'),

                    ]),

                // ── STEP 2: Uraian Temuan ─────────────────────────────────
                Section::make('Uraian Temuan')
                    ->schema([

                        Textarea::make('kondisi')
                            ->label('Kondisi')
                            ->helperText('Apa yang ditemukan? Deskripsikan kondisi yang tidak sesuai kriteria.')
                            ->required()
                            ->minLength(20)
                            ->maxLength(5000)
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('sebab')
                            ->label('Sebab')
                            ->helperText('Mengapa kondisi tersebut terjadi? (root cause)')
                            ->nullable()
                            ->maxLength(3000)
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('akibat')
                            ->label('Akibat')
                            ->helperText('Apa dampak dari kondisi tersebut?')
                            ->nullable()
                            ->maxLength(3000)
                            ->rows(3)
                            ->columnSpanFull(),

                    ]),

                // ── STEP 3: Nilai Kerugian ────────────────────────────────
                Section::make('Nilai Kerugian')
                    ->description('Isi nilai kerugian per kategori sesuai Lampiran 3 Permenpan 42/2011.')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => self::isKeuangan($get('kode_temuan_id')))
                    ->schema([
                        self::makeKerugianField('nilai_kerugian_negara',   'Kerugian Negara (Rp)'),
                        self::makeKerugianField('nilai_kerugian_daerah',   'Kerugian Daerah (Rp)'),
                        self::makeKerugianField('nilai_kerugian_desa',     'Kerugian Desa (Rp)'),
                        self::makeKerugianField('nilai_kerugian_bos_blud', 'Kerugian BOS / BLUD (Rp)'),

                    TextInput::make('nilai_temuan')
    ->label('Total Nilai Temuan (Rp)')
    ->prefix('Rp')
    ->readOnly()
    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0, ',', '.'))
    ->columnSpanFull(),
                    ]),

                // ── STEP 4: Pengembalian Barang ───────────────────────────
                Section::make('Pengembalian Barang / Aset')
                    ->description(
                        'Isi bagian ini jika temuan berkaitan dengan aset atau barang yang harus dikembalikan. ' .
                        'Data ini akan digunakan sebagai referensi saat membuat rekomendasi.'
                    )
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->collapsible()
                    ->visible(fn (Get $get): bool => self::isPotensiBarang($get('kode_temuan_id')))
                    ->schema([

                        TextInput::make('nama_barang')
                            ->label('Nama Barang / Aset')
                            ->helperText('Contoh: Laptop Merk X, Kendaraan Dinas Plat XX 123 XX')
                            ->maxLength(255)
                            ->placeholder('Nama atau jenis barang yang harus dikembalikan'),

                        TextInput::make('jumlah_barang')
                            ->label('Jumlah / Volume')
                            ->helperText('Contoh: 3 unit, 500 kg, 10 m²')
                            ->maxLength(100)
                            ->placeholder('Jumlah dan satuan'),

                        Textarea::make('kondisi_barang')
                            ->label('Kondisi Barang')
                            ->helperText('Deskripsikan kondisi fisik barang saat pemeriksaan.')
                            ->maxLength(1000)
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('lokasi_barang')
                            ->label('Lokasi / Keberadaan Barang')
                            ->helperText('Di mana barang tersebut berada saat ini.')
                            ->maxLength(500)
                            ->rows(2)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

            ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function isKeuangan(?int $kodeTemuanId): bool
    {
        if (! $kodeTemuanId) {
            return true;
        }

        $kode = KodeTemuan::find($kodeTemuanId);
        if (! $kode) {
            return true;
        }

        if (in_array($kode->kel, [2, 3])) {
            return false;
        }

        if ($kode->kel === 1 && $kode->sub_kel === 4) {
            return false;
        }

        return true;
    }

    private static function isPotensiBarang(?int $kodeTemuanId): bool
    {
        if (! $kodeTemuanId) {
            return false;
        }

        $kode = KodeTemuan::find($kodeTemuanId);

        return $kode
            && $kode->kel === 1
            && in_array($kode->sub_kel, [1, 2]);
    }

    private static function makeMoneyMask(): RawJs
{
    return RawJs::make(<<<'JS'
        $money($input, ',', '.', 2)
    JS);
    // $money(value, decimalSymbol, thousandSymbol, precision)
}
private static function makeKerugianField(string $name, string $label): TextInput
{
    return TextInput::make($name)
        ->label($label)
        ->prefix('Rp')
        ->mask(RawJs::make('$money($input)'))
        ->stripCharacters(',')
        ->inputMode('decimal')   // ← JANGAN pakai ->numeric() dengan mask!
        ->default(0)
        ->live(onBlur: true)
        ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungTotal($get, $set));
}

private static function hitungTotal(Get $get, Set $set): void
{
    $total = collect([
        $get('nilai_kerugian_negara'),
        $get('nilai_kerugian_daerah'),
        $get('nilai_kerugian_desa'),
        $get('nilai_kerugian_bos_blud'),
    ])
    ->map(function ($v): float {
        $clean = preg_replace('/[^0-9.]/', '', (string) ($v ?? 0));
        return max(0.0, (float) $clean);
    })
    ->sum();

    // ✅ simpan sebagai float (bukan string)
    $set('nilai_temuan', $total);
}
}