<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cache kalkulasi per LHP.
 * Jangan update manual — selalu via Lhp::refreshStatistik().
 *
 * Tiga metrik persentase:
 *   persen_selesai          = count-based (backward compat)
 *   persen_selesai_nilai    = nilai rupiah rekom uang (include cicilan)
 *   persen_selesai_gabungan = metrik utama, weighted 80/20
 */
class LhpStatistik extends Model
{
    use HasFactory;

    protected $table = 'lhp_statistik';

    protected $fillable = [
        'lhp_id',
        'total_temuan',
        'total_rekomendasi',
        'total_kerugian_negara',
        'total_kerugian_daerah',
        'total_kerugian_desa',
        'total_kerugian_bos_blud',
        'total_kerugian',
        'total_nilai_tl_selesai',
        'total_sisa_kerugian',
        'rekom_belum',
        'rekom_proses',
        'rekom_selesai',
        // Pemisahan uang vs non-uang (kolom baru via sync migration)
        'rekom_uang_total',
        'rekom_uang_selesai',
        'rekom_nonutang_total',
        'rekom_nonutang_selesai',
        // Tiga metrik persentase
        'persen_selesai',           // count-based, backward compat
        'persen_selesai_nilai',     // nilai rupiah, include cicilan
        'persen_selesai_gabungan',  // weighted, metrik utama dashboard
        'dihitung_pada',
    ];

    protected function casts(): array
    {
        return [
            'lhp_id'                  => 'integer',
            'total_temuan'            => 'integer',
            'total_rekomendasi'       => 'integer',
            'total_kerugian_negara'   => 'float',
            'total_kerugian_daerah'   => 'float',
            'total_kerugian_desa'     => 'float',
            'total_kerugian_bos_blud' => 'float',
            'total_kerugian'          => 'float',
            'total_nilai_tl_selesai'  => 'float',
            'total_sisa_kerugian'     => 'float',
            'rekom_belum'             => 'integer',
            'rekom_proses'            => 'integer',
            'rekom_selesai'           => 'integer',
            'rekom_uang_total'        => 'integer',
            'rekom_uang_selesai'      => 'integer',
            'rekom_nonutang_total'    => 'integer',
            'rekom_nonutang_selesai'  => 'integer',
            'persen_selesai'          => 'float',
            'persen_selesai_nilai'    => 'float',
            'persen_selesai_gabungan' => 'float',
            'dihitung_pada'           => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    
  public function lhp(): BelongsTo
{
    return $this->belongsTo(Lhp::class, 'lhp_id', 'id'); // ← BENAR
}
    // ── Accessors ─────────────────────────────────────────────────────────────

    /** Status label berdasarkan metrik gabungan */
    public function getStatusProgressAttribute(): string
    {
        return match (true) {
            $this->persen_selesai_gabungan >= 100 => 'Lunas',
            $this->persen_selesai_gabungan > 0    => 'Sebagian',
            default                               => 'Belum',
        };
    }

    /** Warna badge untuk tabel dan infolist */
    public function getColorProgressAttribute(): string
    {
        return match (true) {
            $this->persen_selesai_gabungan >= 100 => 'success',
            $this->persen_selesai_gabungan >= 50  => 'warning',
            $this->persen_selesai_gabungan > 0    => 'info',
            default                               => 'danger',
        };
    }

    /** Apakah LHP campuran (uang DAN non-uang)? */
    public function getIsMixedAttribute(): bool
    {
        return ($this->rekom_uang_total ?? 0) > 0
            && ($this->rekom_nonutang_total ?? 0) > 0;
    }

    // ── Note ─────────────────────────────────────────────────────────────────
    // Tidak ada booted() — tabel cache murni, write hanya via Lhp::refreshStatistik().
}