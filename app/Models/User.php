<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\AuditAssignment;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    use SoftDeletes;
 protected $fillable = [
        'name',
        'email',
        'password',
        'role',        // Tambahkan ini (sesuai migrasi)
        'avatar_url',
    ];

    // ── Hidden ────────────────────────────────────────────────────────────────
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'deleted_at'        => 'datetime',
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url 
            ? Storage::url($this->avatar_url) 
            : null;
    }

    // ── Role helpers ──────────────────────────────────────────────────────────
    public function isSuperAdmin(): bool        { return $this->role === 'super_admin'; }
    public function isAdminInspektorat(): bool  { return $this->role === 'admin_inspektorat'; }
    public function isIrban(): bool             { return $this->role === 'irban'; }
    public function isKepalaInspektorat(): bool { return $this->role === 'kepala_inspektorat'; }

    public function canVerify(): bool
    {
        return in_array($this->role, ['irban', 'kepala_inspektorat', 'super_admin']);
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function auditPrograms(): HasMany
    {
        return $this->hasMany(AuditProgram::class, 'created_by');
    }

    public function ketuaAssignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class, 'ketua_tim_id');
    }

    public function assignmentMemberships(): BelongsToMany
    {
        return $this->belongsToMany(
            AuditAssignment::class,
            'audit_assignment_members',
            'user_id',
            'audit_assignment_id'
        )->withPivot('jabatan_tim')->withTimestamps();
    }

   public function lhpsDibuat(): HasMany
    {
        return $this->hasMany(Lhp::class, 'created_by');
    }


    public function verifikasiTindakLanjut(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'diverifikasi_oleh');
    }

    public function verifikasiCicilan(): HasMany
    {
        return $this->hasMany(TindakLanjutCicilan::class, 'diverifikasi_oleh');
    }
}
