<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class LhpReport extends Model
{
    use SoftDeletes;

    protected $table = 'lhps';
    protected $primaryKey = 'id';

    protected $fillable = [
        'audit_assignment_id', 'nomor_lhp', 'tanggal_lhp', 'semester',
        'jenis_pemeriksaan', 'catatan_umum', 'keterangan', 'irban',
        'status', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lhp' => 'date',
            'deleted_at'  => 'datetime',
        ];
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function auditAssignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class, 'audit_assignment_id', 'id');
    }

    public function statistik(): HasOne
    {
        return $this->hasOne(LhpStatistik::class, 'lhp_id', 'id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeSelesai(Builder $query): Builder
    {
        return $query
            ->whereIn('status', ['final', 'ditandatangani'])
            ->has('auditAssignment');
    }

    public function scopeSelesai100(Builder $query): Builder  // ← fix: $query bukan $this
    {
        return $query
            ->whereIn('status', ['final', 'ditandatangani'])
            ->has('auditAssignment')
            ->whereHas('statistik', fn (Builder $q) => 
                $q->where('persen_selesai_gabungan', 100)
            );
    }
}