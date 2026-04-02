<?php

namespace App\Models;

use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Traits\HasActivityLog;

class TindakLanjut extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments,HasActivityLog;
     protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'tindak_lanjuts';

    protected $fillable = [
        'recommendation_id', 'jenis_penyelesaian', 'is_cicilan', 'nilai_tindak_lanjut',
        'jumlah_cicilan_rencana', 'tanggal_mulai_cicilan', 'tanggal_jatuh_tempo',
        'nilai_per_cicilan_rencana', 'jumlah_cicilan_realisasi', 'total_terbayar',
        'sisa_belum_bayar', 'catatan_tl', 'hambatan', 'status_verifikasi',
        'diverifikasi_oleh', 'diverifikasi_pada', 'catatan_verifikasi',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_cicilan'                => 'boolean',
            'jumlah_cicilan_rencana'    => 'integer',
            'jumlah_cicilan_realisasi'  => 'integer',
            'tanggal_mulai_cicilan'     => 'date',
            'tanggal_jatuh_tempo'       => 'date',
            'nilai_tindak_lanjut'       => 'decimal:2',
            'nilai_per_cicilan_rencana' => 'decimal:2',
            'total_terbayar'            => 'decimal:2',
            'sisa_belum_bayar'          => 'decimal:2',
            'diverifikasi_pada'         => 'datetime',
        ];
    }
    protected $attributes = [
    'nilai_tindak_lanjut' => 0,
    'total_terbayar' => 0,
    'sisa_belum_bayar' => 0,
    'status_verifikasi' => 'menunggu_verifikasi',
];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function recommendation(): BelongsTo 
    { 
        return $this->belongsTo(Recommendation::class); 
    }

public function scopeForUser($query, $user)
{
    if ($user->hasRole('super_admin')) {
        return $query;
    }

    return $query->whereHas('recommendation.temuan.lhp.auditAssignment', function ($q) use ($user) {
        $q->where('ketua_tim_id', $user->id)
          ->orWhereHas('members', function ($q2) use ($user) {
              $q2->where('audit_assignment_members.user_id', $user->id); // ✅ prefix tabel
          });
    });
}

    public function verifikator(): BelongsTo 
    { 
        return $this->belongsTo(User::class, 'diverifikasi_oleh'); 
    }

    public function cicilans(): HasMany
    {
        return $this->hasMany(TindakLanjutCicilan::class, 'tindak_lanjut_id')->orderBy('ke');
    }

    public function cicilanDiterima(): HasMany
    {
        return $this->cicilans()->where('status', 'diterima');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function nextKeCicilan(): int
    {
        return ($this->cicilans()->max('ke') ?? 0) + 1;
    }

public function syncCalculations(): static
{
    $rekom      = $this->recommendation;
    $nilaiRekom = (float) ($rekom?->nilai_rekom ?? 0);
    $isCicilan  = $this->jenis_penyelesaian === 'cicilan';

    if ($isCicilan) {
        // ✅ Selalu query DB langsung, hindari stale collection
        $this->total_terbayar           = (float) $this->cicilanDiterima()->sum('nilai_bayar');
        $this->jumlah_cicilan_realisasi = $this->cicilanDiterima()->count();
    } else {
        $this->total_terbayar           = $this->status_verifikasi === 'lunas'
            ? (float) ($this->nilai_tindak_lanjut ?? 0)
            : 0;
        $this->jumlah_cicilan_realisasi = 0;
    }

    $this->sisa_belum_bayar = max(0, $nilaiRekom - $this->total_terbayar);

    // ✅ Fix: tambahkan else agar status tidak "tergantung" nilai lama
    if ($this->sisa_belum_bayar <= 0 && $nilaiRekom > 0) {
        $this->status_verifikasi = 'lunas';
    } elseif ($this->total_terbayar > 0 && $this->total_terbayar < $nilaiRekom) {
        $this->status_verifikasi = 'berjalan';
    } else {
        // ✅ Tambahan: reset ke menunggu jika belum ada pembayaran
        if ($this->total_terbayar <= 0 && $isCicilan) {
            $this->status_verifikasi = 'menunggu_verifikasi';
        }
    }

    return $this;
}

public function getIsCicilanAttribute(): bool
{
    return $this->jenis_penyelesaian === 'cicilan';
}
   public function progress(): float
{
    if ($this->status_verifikasi === 'lunas') {
        return 100; // ✅ FIX CEPAT & AMAN
    }

    $rekom = $this->recommendation;
    if (! $rekom) return 0;

    if ($rekom->isUang()) {
        $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);

        return $nilaiRekom > 0
            ? round($this->total_terbayar / $nilaiRekom * 100, 2)
            : 0;
    }

    return 0;
}

    // ── Events ────────────────────────────────────────────────────────────────

   // app/Models/TindakLanjut.php

// ✅ Fix booted() — is_cicilan sekarang computed dari jenis_penyelesaian
protected static function booted(): void
{
    // ✅ WAJIB: sync sebelum save ke DB
    static::saving(function (self $model) {
        $model->syncCalculations();
    });

    static::saved(function (self $model) {

        // ✅ OPTIMASI: jangan query ulang kalau sudah ada relasi
        $rekom = $model->relationLoaded('recommendation')
            ? $model->recommendation
            : $model->recommendation()->first();

        if (! $rekom) return;

        $nilaiRekom = (float) $rekom->nilai_rekom;
        $isCicilan  = $model->jenis_penyelesaian === 'cicilan';

        if ($rekom->isUang()) {

            if (! $isCicilan) {

                // ✅ FIX: pakai total_terbayar
                $total = (float) $rekom->tindakLanjuts()
                    ->where('jenis_penyelesaian', '!=', 'cicilan')
                    ->where('status_verifikasi', 'lunas')
                    ->sum('total_terbayar'); // 🔥 FIX UTAMA

                $rekom->nilai_tl_selesai = min($total, $nilaiRekom);
                $rekom->nilai_sisa       = max(0, $nilaiRekom - $rekom->nilai_tl_selesai);

                $rekom->status = match (true) {
                    $nilaiRekom > 0 && $total >= $nilaiRekom => 'selesai',
                    $total > 0 || $rekom->tindakLanjuts()
                        ->whereIn('status_verifikasi', ['menunggu_verifikasi', 'berjalan'])
                        ->exists() => 'proses',
                    default => 'belum',
                };

            } else {

                $totalDiterima = (float) DB::table('tindak_lanjut_cicilans')
                    ->join('tindak_lanjuts', 'tindak_lanjuts.id', '=', 'tindak_lanjut_cicilans.tindak_lanjut_id')
                    ->where('tindak_lanjuts.recommendation_id', $rekom->id)
                    ->where('tindak_lanjut_cicilans.status', 'diterima')
                    ->whereNull('tindak_lanjut_cicilans.deleted_at')
                    ->whereNull('tindak_lanjuts.deleted_at')
                    ->sum('tindak_lanjut_cicilans.nilai_bayar');

                $rekom->nilai_tl_selesai = min($totalDiterima, $nilaiRekom);
                $rekom->nilai_sisa       = max(0, $nilaiRekom - $rekom->nilai_tl_selesai);

                $rekom->status = ($totalDiterima >= $nilaiRekom && $nilaiRekom > 0)
                    ? 'selesai'
                    : 'proses';
            }

        } else {

            $hasLunas = $rekom->tindakLanjuts()
                ->where('status_verifikasi', 'lunas')
                ->exists();

            $rekom->status = $hasLunas
                ? 'selesai'
                : ($rekom->tindakLanjuts()->exists() ? 'proses' : 'belum');
        }

        $rekom->saveQuietly();

        // ✅ SYNC TEMUAN + LHP
        if ($temuan = $rekom->temuan()->first()) {
            $temuan->syncStatus();

            if ($temuan->lhp_id) {
                app(\App\Services\LhpStatistikService::class)
                    ->updateStatistik($temuan->lhp_id);
            }
        }
    });
}
}