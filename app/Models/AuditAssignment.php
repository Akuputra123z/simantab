<?php

namespace App\Models;

use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\HasActivityLog;
use App\Traits\HasAttachments;


class AuditAssignment extends Model
{
    use HasFactory, HasAttachments, SoftDeletes, HasCreatedUpdatedBy, HasActivityLog;
        protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];


    protected $fillable = [
        'audit_program_id',
        'unit_diperiksa_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'ketua_tim_id',
        'nama_tim',
        'nomor_surat',
        'status',
        'created_by',
        'updated_by',
    ];
    

    protected function casts(): array
    {
        return [
            'tanggal_mulai'   => 'date',
            'tanggal_selesai' => 'date',
            'deleted_at'      => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function getLabelAttribute(): string
{
    return "{$this->unitDiperiksa?->nama_unit} — {$this->nama_tim} | {$this->nomor_surat}";
}

    
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeBerjalan(Builder $query): Builder
    {
        return $query->where('status', 'berjalan');
    }

    /**
     * Assignment yang tanggal pemeriksaannya mencakup tanggal tertentu.
     */
    public function scopeAktifPada(Builder $query, string $tanggal): Builder
    {
        return $query
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function auditProgram(): BelongsTo
    {
        return $this->belongsTo(
            AuditProgram::class,
            'audit_program_id'
        );
    }

    public function unitDiperiksa(): BelongsTo
    {
        return $this->belongsTo(
            UnitDiperiksa::class,
            'unit_diperiksa_id'
        );
    }

    public function ketuaTim(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'ketua_tim_id'
        );
    }

    /**
     * Anggota tim audit.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'audit_assignment_members',
            'audit_assignment_id',
            'user_id'
        )
        ->withPivot('jabatan_tim')
        ->withTimestamps();
    }

    public function lhps(): HasMany
    {
        return $this->hasMany(
            Lhp::class,
            'audit_assignment_id'
        );
    }

    /**
     * LHP final / ditandatangani untuk assignment ini.
     */
    public function lhpFinal(): HasOne
    {
        return $this->hasOne(
            Lhp::class,
            'audit_assignment_id'
        )
        ->whereIn('status', ['final', 'ditandatangani'])
        ->latestOfMany();
    }
}