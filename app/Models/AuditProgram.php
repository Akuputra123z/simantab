<?php

namespace App\Models;

use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Traits\HasActivityLog;

class AuditProgram extends Model
{
    use HasFactory, SoftDeletes, HasCreatedUpdatedBy, HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];


    protected $fillable = [
        'nama_program',
        'tahun',
        'status',
        'created_by',
        'updated_by',
        'target_assignment',
    ];

    // ✅ AUTO APPEND KE JSON / FILAMENT
    protected $appends = [
        'realisasi_assignment',
        'sudah_lhp',
        'sisa_target',
        'progress',
    ];

    protected function casts(): array
    {
        return [
            'tahun'              => 'integer',
            'target_assignment'  => 'integer',
            'deleted_at'         => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeBerjalan(Builder $query): Builder
    {
        return $query->where('status', 'berjalan');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function assignments(): HasMany
    {
        return $this->hasMany(
            AuditAssignment::class,
            'audit_program_id'
        );
    }

    public function lhps(): HasManyThrough
    {
        return $this->hasManyThrough(
            Lhp::class,
            AuditAssignment::class,
            'audit_program_id',
            'audit_assignment_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (🔥 CORE FITUR)
    |--------------------------------------------------------------------------
    */

    // ✅ Realisasi (gunakan eager count kalau ada)
    public function getRealisasiAssignmentAttribute(): int
    {
        return $this->assignments_count
            ?? $this->assignments()->count();
    }

    // ✅ Sudah LHP (optimize query)
    public function getSudahLhpAttribute(): int
    {
        return $this->assignments()
            ->has('lhps')
            ->count();
    }

    // ✅ Sisa target
    public function getSisaTargetAttribute(): int
    {
        return max(0, ($this->target_assignment ?? 0) - $this->realisasi_assignment);
    }

    // ✅ Progress %
    public function getProgressAttribute(): float
    {
        $target = $this->target_assignment ?? 0;

        if ($target === 0) {
            return 0;
        }

        return round(($this->sudah_lhp / $target) * 100, 1);
    }
}