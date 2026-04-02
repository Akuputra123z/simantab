<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KodeTemuan extends Model
{
    use SoftDeletes;

    protected $table = 'kode_temuans';

    protected $fillable = [
        'kode',
        'kode_numerik',
        'kel',
        'sub_kel',
        'jenis',
        'kelompok',
        'sub_kelompok',
        'deskripsi',
        'alternatif_rekom',
    ];

    protected function casts(): array
    {
        return [
            'kel'              => 'integer',
            'sub_kel'          => 'integer',
            'jenis'            => 'integer',
            'alternatif_rekom' => 'array',
            'deleted_at'       => 'datetime',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    /** Filter berdasarkan kelompok: 1=Ketidakpatuhan, 2=SPI, 3=3E */
    public function scopeKelompok($query, int $kel)
    {
        return $query->where('kel', $kel);
    }

    public function scopeKetidakpatuhan($query) { return $query->kelompok(1); }
    public function scopeSpi($query)            { return $query->kelompok(2); }
    public function scopeTigaE($query)          { return $query->kelompok(3); }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function temuans(): HasMany
    {
        return $this->hasMany(Temuan::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getLabelKelompokAttribute(): string
    {
        return match ($this->kel) {
            1 => 'Ketidakpatuhan',
            2 => 'Kelemahan SPI',
            3 => '3E (Ekonomis, Efisien, Efektif)',
            default => 'Tidak Diketahui',
        };
    }

    public function getLabelAttribute(): string
    {
        return "{$this->kode} — {$this->deskripsi}";
    }
}
