<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasActivityLog;
class Temuan extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments, HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'temuans';

    protected $fillable = [
        'lhp_id', 'kode_temuan_id', 'kondisi', 'sebab', 'akibat',
        'nilai_temuan', 'nilai_kerugian_negara', 'nilai_kerugian_daerah',
        'nilai_kerugian_desa', 'nilai_kerugian_bos_blud', 'status_tl','nama_barang','jumlah_barang','kondisi_barang','lokasi_barang',
        'created_by', 'updated_by',
    ];

    // Tambahkan ini agar atribut terbaca oleh Filament
    protected $appends = ['total_nilai_temuan'];

    protected function casts(): array
    {
        return [
            'nilai_temuan'            => 'decimal:2',
            'nilai_kerugian_negara'   => 'decimal:2',
            'nilai_kerugian_daerah'   => 'decimal:2',
            'nilai_kerugian_desa'     => 'decimal:2',
            'nilai_kerugian_bos_blud' => 'decimal:2',
            'deleted_at'              => 'datetime',
        ];
    }

    // ── Constants ─────────────────────────────────────────────────────────────

    public const STATUS_BELUM   = 'belum_ditindaklanjuti';
    public const STATUS_PROSES  = 'dalam_proses';
    public const STATUS_SELESAI = 'selesai';

    // ── Klasifikasi ──────────────────────────────────────────────────────────

    public function isKeuangan(): bool
    {
        return (float) $this->nilai_temuan > 0;
    }

    public function isPotensiPengembalianBarang(): bool
    {
        $kel    = $this->kodeTemuan?->kel;
        $subKel = $this->kodeTemuan?->sub_kel;
        return $kel === 1 && in_array($subKel, [1, 2]);
    }

    public function jenisRekomendasiDefault(): string
    {
        $kel    = $this->kodeTemuan?->kel;
        $subKel = $this->kodeTemuan?->sub_kel;

        return match (true) {
            $kel === 2, $kel === 3               => 'admin',
            $kel === 1 && $subKel === 4          => 'admin',
            $kel === 1 && $subKel === 5          => 'admin',
            default                              => 'uang',
        };
    }


    public function getStatusTlFinalAttribute(): string
{
    return $this->status_tl ?? 'belum';
}
    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeStatusTl(Builder $q, string $s): Builder { return $q->where('status_tl', $s); }
    public function scopeBelumDitindaklanjuti(Builder $q): Builder { return $q->where('status_tl', self::STATUS_BELUM); }
    public function scopeDalamProses(Builder $q): Builder          { return $q->where('status_tl', self::STATUS_PROSES); }
    public function scopeSelesai(Builder $q): Builder              { return $q->where('status_tl', self::STATUS_SELESAI); }

    // ── Relationships ─────────────────────────────────────────────────────────
public function lhp(): BelongsTo
{
    return $this->belongsTo(Lhp::class, 'lhp_id', 'id');
    //                                   ^ FK di temuans  ^ PK di lhps
}
    public function kodeTemuan(): BelongsTo   { return $this->belongsTo(KodeTemuan::class); }
    public function recommendations(): HasMany { return $this->hasMany(Recommendation::class); }
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('urutan');
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    /**
     * Accessor untuk nilai total temuan (Penting untuk Form Rekomendasi)
     */
    public function getTotalNilaiTemuanAttribute(): float
    {
        return (float) ($this->nilai_kerugian_negara ?? 0) + 
               (float) ($this->nilai_kerugian_daerah ?? 0) + 
               (float) ($this->nilai_kerugian_desa ?? 0) + 
               (float) ($this->nilai_kerugian_bos_blud ?? 0);
    }

    public function getTotalKerugianAttribute(): string
    {
        return (string) $this->total_nilai_temuan;
    }

    public function progress(): float
    {
        $total = $this->recommendations->count();
        if ($total === 0) return 0;
        return round($this->recommendations->where('status', 'selesai')->count() / $total * 100, 2);
    }

    // ── syncStatus ────────────────────────────────────────────────────────────

    public function syncStatus(): void
    {
        // Pastikan nilai-nilai numerik tidak NULL untuk mencegah SQL Error 1048
        $this->nilai_temuan            = $this->nilai_temuan ?? 0;
        $this->nilai_kerugian_negara   = $this->nilai_kerugian_negara ?? 0;
        $this->nilai_kerugian_daerah   = $this->nilai_kerugian_daerah ?? 0;
        $this->nilai_kerugian_desa     = $this->nilai_kerugian_desa ?? 0;
        $this->nilai_kerugian_bos_blud = $this->nilai_kerugian_bos_blud ?? 0;

        $allRekom = $this->recommendations()->get(); 
        $total    = $allRekom->count();
        
        // Sesuaikan dengan konstanta status yang ada di model Recommendation
        $selesai  = $allRekom->where('status', 'selesai')->count();
        $proses   = $allRekom->whereIn('status', ['proses', 'dalam_proses', 'berjalan'])->count();

        if ($total > 0) {
            if ($selesai === $total) {
                $this->status_tl = self::STATUS_SELESAI; 
            } elseif ($selesai > 0 || $proses > 0) {
                $this->status_tl = self::STATUS_PROSES;
            } else {
                $this->status_tl = self::STATUS_BELUM;
            }
        } else {
            $this->status_tl = self::STATUS_BELUM;
        }

        // Gunakan save() biasa agar booted() terpanggil untuk update LHP
        // Atau jika ingin tetap saveQuietly, pastikan Service Statistik dipanggil manual
        $this->saveQuietly();

        if ($this->lhp_id) {
            // Memastikan statistik LHP sinkron setelah status temuan berubah
            app(\App\Services\LhpStatistikService::class)->updateStatistik($this->lhp_id);
        }
    }
    // ── Events ────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Gunakan updateStatistik dari Service agar lebih konsisten dengan syncStatus
        static::saved(function (self $model) {
            if ($model->lhp_id) {
                app(\App\Services\LhpStatistikService::class)->updateStatistik($model->lhp_id);
            }
        });
    }
}