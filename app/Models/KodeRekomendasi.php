<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KodeRekomendasi extends Model
{
    use HasFactory;

    protected $table = 'kode_rekomendasis';

    protected $fillable = [
        'kode',
        'kode_numerik',
        'kategori',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'kode_numerik' => 'integer',
            'is_active'    => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function recommendations(): HasMany
    {
        return $this->hasMany(
            Recommendation::class,
            'kode_rekomendasi_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getLabelAttribute(): string
    {
        return "{$this->kode} — {$this->deskripsi}";
    }
}