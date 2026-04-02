<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\LhpStatistikService;
use App\Traits\HasActivityLog;

class Lhp extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments,HasActivityLog;

    protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'lhps';
    protected $primaryKey = 'id';


    protected $fillable = [
        'audit_assignment_id', 'nomor_lhp', 'tanggal_lhp', 'semester',
        'jenis_pemeriksaan', 'catatan_umum', 'keterangan', 'irban','status_batal_keterangan',  
        'status_batal_user_id','status_batal_at',  'status', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lhp' => 'date',
            'semester' => 'integer',
            'deleted_at' => 'datetime',
            'status_batal_at' => 'datetime',
        ];
    }

    public function batalUser(): BelongsTo
{
    return $this->belongsTo(User::class, 'status_batal_user_id');
}

public function attachments(): HasMany
{
    return $this->hasMany(Attachment::class, 'lhp_id');
}

// Lhp.php
public function getStatusBatalInfoAttribute(): string
{
    if (! $this->status_batal_at) return '-';
    $user = $this->batalUser?->name ?? 'Unknown';
    return "Dibatalkan oleh {$user} pada " . $this->status_batal_at->format('d M Y H:i') .
           ($this->status_batal_keterangan ? " (Alasan: {$this->status_batal_keterangan})" : '');
}

    // --- Relations ---
    public function auditAssignment(): BelongsTo { return $this->belongsTo(AuditAssignment::class); }
    public function creator(): BelongsTo         { return $this->belongsTo(User::class, 'created_by'); }
    public function temuans(): HasMany           { return $this->hasMany(Temuan::class); }
    // public function statistik(): HasOne          { return $this->hasOne(LhpStatistik::class); }
    public function reports(): HasMany           { return $this->hasMany(LhpReport::class); }

   public function recommendations(): HasManyThrough
{
    // Parameter ke-3 adalah foreign key di tabel temuans (lhp_id)
    // Parameter ke-4 adalah foreign key di tabel recommendations (temuan_id)
    // Parameter ke-5 adalah local key di tabel lhps (id)
    return $this->hasManyThrough(
        Recommendation::class, 
        Temuan::class, 
        'lhp_id', 
        'temuan_id', 
        'id', 
        'id'
    );
}

// Lhp.php
public function statistik(): HasOne
{
    return $this->hasOne(LhpStatistik::class, 'lhp_id', 'id'); // eksplisit
}
    // --- Scopes ---
    public function scopeStatus(Builder $q, string $s): Builder { return $q->where('status', $s); }
    public function scopeSemester(Builder $q, int $s): Builder  { return $q->where('semester', $s); }
    public function scopeIrban(Builder $q, string $i): Builder  { return $q->where('irban', $i); }

    // --- Attributes ---
    public function getTotalKerugianAttribute(): float
    {
        return (float) ($this->statistik?->total_kerugian ?? 0);
    }

    public function getPersenSelesaiAttribute(): float
    {
        return (float) ($this->statistik?->persen_selesai_gabungan ?? 0);
    }
    public function scopeForUser(Builder $query, ?User $user): Builder
{
    if (! $user) {
        return $query->whereRaw('1 = 0');
    }

    if ($user->hasRole('super_admin')) {
        return $query;
    }

    return $query->whereHas('auditAssignment', function ($q) use ($user) {
        $q->where('ketua_tim_id', $user->id)
          ->orWhereHas('members', function ($q2) use ($user) {
              $q2->where('user_id', $user->id); // ✅ FIX pivot
          });
    });
}

    /**
     * Pintu masuk tunggal untuk hitung statistik.
     */
    public function refreshStatistik(): void
    {
        // Memanggil updateStatistik di Service
        app(LhpStatistikService::class)->updateStatistik($this->id);
    }

    /**
     * Alias untuk menghindari error 'Call to undefined method hitung()'
     */
    public function hitung(): void
    {
        $this->refreshStatistik();
    }

    /** @deprecated Gunakan refreshStatistik() */
    public function updateStatistik(): void 
    { 
        $this->refreshStatistik(); 
    }

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            // Update statistik jika LHP baru dibuat atau ada perubahan link penugasan
            if ($model->wasRecentlyCreated || $model->isDirty('audit_assignment_id')) {
                $model->refreshStatistik();
            }
        });
    }

    public function scopeSelesai(Builder $q): Builder
{
    return $q
        ->whereIn('status', ['final', 'ditandatangani'])
        ->has('auditAssignment');
}

public function scopeLaporanSelesai(Builder $q): Builder
{
    return $q
        ->whereIn('status', ['final', 'ditandatangani'])
        ->has('auditAssignment')
        ->whereHas('statistik', fn (Builder $s) =>
            $s->where('persen_selesai_gabungan', 100)
        );
}
}