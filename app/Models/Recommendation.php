<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasActivityLog;
class Recommendation extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments,HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'recommendations';

    protected $fillable = [
        'temuan_id', 'kode_rekomendasi_id', 'uraian_rekom', 'jenis_rekomendasi',
        'nilai_rekom', 'nilai_tl_selesai', 'nilai_sisa', 'batas_waktu', 'status',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai_rekom'      => 'decimal:2',
            'nilai_tl_selesai' => 'decimal:2',
            'nilai_sisa'       => 'decimal:2',
            'batas_waktu'      => 'date',
            'deleted_at'       => 'datetime',
        ];
    }

    // ── Constants ─────────────────────────────────────────────────────────────

    public const STATUS_BELUM   = 'belum_ditindaklanjuti';
    public const STATUS_PROSES  = 'proses';
    public const STATUS_SELESAI = 'selesai';

    public const JENIS_UANG   = 'uang';
    public const JENIS_BARANG = 'barang';
    public const JENIS_ADMIN  = 'administrasi';

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isUang(): bool    { return $this->jenis_rekomendasi === self::JENIS_UANG; }
    public function isNonUang(): bool { return ! $this->isUang(); }

    /**
     * Sinkronisasi Status & Nilai berdasarkan data Tindak Lanjut.
     * Inilah "Kunci" agar Dashboard LHP sinkron.
     */
    // app/Models/Recommendation.php
public function kodeRekomendasi(): BelongsTo
{
    return $this->belongsTo(KodeRekomendasi::class, 'kode_rekomendasi_id');
}

    public function isJatuhTempo(): bool
{
    // Jika tidak ada inputan batas waktu, maka tidak dianggap jatuh tempo
    if (! $this->batas_waktu) {
        return false;
    }

    // Menggunakan Carbon (isPast) untuk cek apakah tanggal sudah lewat
    // Dan pastikan statusnya BUKAN 'selesai'
    return $this->batas_waktu->isPast() 
        && $this->status !== self::STATUS_SELESAI;
}
public function syncStatus(): void
{
    $tindakLanjuts = $this->tindakLanjuts();
    
    $hasLunas  = (clone $tindakLanjuts)->where('status_verifikasi', 'lunas')->exists();
    $hasProses = (clone $tindakLanjuts)->whereIn('status_verifikasi', ['menunggu_verifikasi', 'berjalan'])->exists();

    if ($this->isUang()) {
        $nilaiRekom = (float) ($this->nilai_rekom ?? 0);
        $totalLunas = (float) (clone $tindakLanjuts)
            ->where('status_verifikasi', 'lunas')
            ->sum('nilai_tindak_lanjut');

        $this->nilai_tl_selesai = $totalLunas;
        $this->nilai_sisa       = max(0, $nilaiRekom - $totalLunas);

        $this->status = match (true) {
            $nilaiRekom > 0 && $totalLunas >= $nilaiRekom => self::STATUS_SELESAI,
            $nilaiRekom <= 0 && $hasLunas                 => self::STATUS_SELESAI,
            $hasLunas || $hasProses                       => self::STATUS_PROSES,
            default                                       => self::STATUS_BELUM,
        };
    } else {
        if ($hasLunas) {
            $this->status           = self::STATUS_SELESAI;
            $this->nilai_tl_selesai = 1;
            $this->nilai_sisa       = 0;
        } elseif ($hasProses) {
            $this->status           = self::STATUS_PROSES;
            $this->nilai_tl_selesai = 0;
            $this->nilai_sisa       = 1;
        } else {
            $this->status           = self::STATUS_BELUM;
            $this->nilai_tl_selesai = 0;
            $this->nilai_sisa       = 1;
        }
    }

    // ✅ saveQuietly() — tidak trigger booted(), tidak ada infinite loop
    $this->saveQuietly();
    
    // ✅ Cascade ke temuan setelah rekom tersimpan
    $this->temuan?->syncStatus();
}
    public function progress(): float
    {
        if ($this->isUang()) {
            $nilaiRekom = (float) $this->nilai_rekom;
            return $nilaiRekom > 0
                ? round((float)$this->nilai_tl_selesai / $nilaiRekom * 100, 2)
                : 0;
        }
        return $this->status === self::STATUS_SELESAI ? 100 : 0;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function temuan(): BelongsTo       { return $this->belongsTo(Temuan::class); }
    public function tindakLanjuts(): HasMany  { return $this->hasMany(TindakLanjut::class); }
    
    // ── Events ────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            // Memicu hitung ulang LHP jika ada perubahan data dasar
            $model->temuan?->lhp?->refreshStatistik();
        });
    }
}