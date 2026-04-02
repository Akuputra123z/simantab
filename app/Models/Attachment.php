<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Polymorphic keys — WAJIB fillable agar Repeater->relationship() bisa save
        'attachable_type',
        'attachable_id',
        // File info
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        // Grouping & display
        'jenis_bukti',
        'urutan',
        'keterangan',
        'visibilitas',
        // Audit trail
        'uploaded_by',
    ];

    protected $casts = [
        'file_size'  => 'integer',
        'urutan'     => 'integer',
        'deleted_at' => 'datetime',
    ];

    // ── Jenis Bukti Constants ─────────────────────────────────────────────────
    // Gunakan konstanta spesifik di form masing-masing.
    // Array JENIS_BUKTI tersedia untuk backward-compatibility.

    public const JENIS_BUKTI_LHP = [
        'draft_lhp'      => 'Draft LHP',
        'lhp_final'      => 'LHP Final',
        'surat_pengantar' => 'Surat Pengantar',
    ];

    public const JENIS_BUKTI_TEMUAN = [
        'foto_kondisi'      => 'Foto Kondisi',
        'dokumen_pendukung' => 'Dokumen Pendukung',
        'ba_pemeriksaan'    => 'BA Pemeriksaan',
    ];

    public const JENIS_BUKTI_TINDAK_LANJUT = [
        'bukti_transfer'    => 'Bukti Transfer',
        'kwitansi'          => 'Kwitansi',
        'surat_pernyataan'  => 'Surat Pernyataan',
        'dokumen_pendukung' => 'Dokumen Pendukung',
        'foto_bukti'        => 'Foto Bukti',
    ];

    public const JENIS_BUKTI_CICILAN = [
        'bukti_transfer' => 'Bukti Transfer',
        'kwitansi'       => 'Kwitansi',
        'bukti_setor'    => 'Bukti Setor Kas',
        'foto'           => 'Foto Fisik',
        'lainnya'        => 'Lainnya',
    ];

    /**
     * Backward-compatible — akses via Attachment::JENIS_BUKTI['tindak_lanjut']
     */
    public const JENIS_BUKTI = [
        'lhp'           => self::JENIS_BUKTI_LHP,
        'temuan'        => self::JENIS_BUKTI_TEMUAN,
        'tindak_lanjut' => self::JENIS_BUKTI_TINDAK_LANJUT,
        'cicilan'       => self::JENIS_BUKTI_CICILAN,
    ];

    // ── Boot: auto-set uploaded_by ────────────────────────────────────────────

   protected static function booted(): void
{
    static::creating(function (self $attachment) {
        if (auth()->check()) {
            $attachment->uploaded_by ??= auth()->id();
        }
    });

    // TAMBAHKAN INI: Hapus file fisik saat record database dihapus permanen
    static::deleted(function (self $attachment) {
        // Jika force delete, hapus file dari storage
        if ($attachment->isForceDeleting()) {
            if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }
    });
}

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ── Accessor ──────────────────────────────────────────────────────────────

    public function getFileSizeFormattedAttribute(): string
    {
        if (! $this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = $this->file_size;
        $unit  = 0;

        while ($size >= 1024 && $unit < 3) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->file_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }
}