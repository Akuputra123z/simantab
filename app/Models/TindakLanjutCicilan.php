<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAttachments;
use App\Traits\HasCreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class TindakLanjutCicilan extends Model
{
    use SoftDeletes, HasCreatedUpdatedBy, HasAttachments, HasActivityLog;

     protected static $logExcept = ['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'];


    protected $table = 'tindak_lanjut_cicilans';

    protected $fillable = [
        'tindak_lanjut_id',
        'ke',
        'nilai_bayar',
        'tanggal_bayar',    
        'tanggal_jatuh_tempo_cicilan',
        'nomor_bukti',
        'keterangan',
        'jenis_bayar',
        'nilai_bayar_negara',
        'nilai_bayar_daerah',
        'nilai_bayar_desa',
        'nilai_bayar_bos_blud',
        'status',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'catatan_verifikasi',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'ke'                          => 'integer',
            'nilai_bayar'                 => 'decimal:2',
            'nilai_bayar_negara'          => 'decimal:2',
            'nilai_bayar_daerah'          => 'decimal:2',
            'nilai_bayar_desa'            => 'decimal:2',
            'nilai_bayar_bos_blud'        => 'decimal:2',
            'tanggal_bayar'               => 'date',
            'tanggal_jatuh_tempo_cicilan' => 'date',
            'diverifikasi_pada'           => 'datetime',
        ];
    }

    const STATUS_MENUNGGU = 'menunggu_verifikasi';
    const STATUS_DITERIMA = 'diterima';
    const STATUS_DITOLAK  = 'ditolak';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function tindakLanjut(): BelongsTo
    {
        return $this->belongsTo(TindakLanjut::class, 'tindak_lanjut_id');
    }

    public function diverifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    // ── Events ────────────────────────────────────────────────────────────────

 protected static function booted(): void
{
    static::creating(function (self $model) {
        if (! $model->ke) {
            $lastKe    = static::where('tindak_lanjut_id', $model->tindak_lanjut_id)->max('ke');
            $model->ke = ($lastKe ?? 0) + 1;
        }
    });

    static::deleted(function (self $model) {
        static::where('tindak_lanjut_id', $model->tindak_lanjut_id)
            ->where('ke', '>', $model->ke)
            ->decrement('ke');
    });

    $cascade = function (self $model): void {
    $tl = TindakLanjut::find($model->tindak_lanjut_id);
    if (! $tl) return;

    // ✅ Gunakan saveQuietly() agar tidak trigger saving/saved lagi
    // yang menyebabkan syncCalculations() dipanggil dua kali
    $tl->syncCalculations()->saveQuietly();
    
    // ✅ Sync recommendation dan LHP secara eksplisit setelah TL tersimpan
    $tl->recommendation?->syncStatus();
};

    static::saved($cascade);
    static::deleted($cascade);
    static::restored($cascade);

    // ✅ cascadeToRekom() DIHAPUS — sudah tidak dipakai, diganti $cascade di atas
}
    protected static function cascadeToRekom(TindakLanjut $tl): void
    {
        $rekom = $tl->recommendation()->first();

        if (! $rekom) {
            return;
        }

        if ($rekom->isUang()) {
            $totalCicilan = (float) DB::table('tindak_lanjut_cicilans')
                ->join('tindak_lanjuts', 'tindak_lanjuts.id', '=', 'tindak_lanjut_cicilans.tindak_lanjut_id')
                ->where('tindak_lanjuts.recommendation_id', $rekom->id)
                ->where('tindak_lanjuts.is_cicilan', true)
                ->where('tindak_lanjut_cicilans.status', self::STATUS_DITERIMA)
                ->whereNull('tindak_lanjut_cicilans.deleted_at')
                ->whereNull('tindak_lanjuts.deleted_at')
                ->sum('tindak_lanjut_cicilans.nilai_bayar');

            $totalNonCicilan = (float) DB::table('tindak_lanjuts')
                ->where('recommendation_id', $rekom->id)
                ->where('is_cicilan', false)
                ->where('status_verifikasi', '!=', self::STATUS_DITOLAK)
                ->whereNull('deleted_at')
                ->sum('total_terbayar');

            $total      = $totalCicilan + $totalNonCicilan;
            $nilaiRekom = (float) $rekom->nilai_rekom;

            $rekom->nilai_tl_selesai = $total;
            $rekom->nilai_sisa       = max(0, $nilaiRekom - $total);
            $rekom->status           = match (true) {
                $nilaiRekom > 0 && $total >= $nilaiRekom => Recommendation::STATUS_SELESAI,
                $total > 0                               => Recommendation::STATUS_PROSES,
                default                                  => Recommendation::STATUS_BELUM,
            };

        } else {
            $counts = DB::table('tindak_lanjuts')
                ->where('recommendation_id', $rekom->id)
                ->whereNull('deleted_at')
                ->selectRaw("
                    SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) as lunas,
                    SUM(CASE WHEN status_verifikasi IN ('menunggu_verifikasi','berjalan') THEN 1 ELSE 0 END) as proses
                ")
                ->first();

            $hasLunas   = ($counts->lunas ?? 0) > 0;
            $hasProses  = ($counts->proses ?? 0) > 0;
            $nilaiRekom = (float) ($rekom->nilai_rekom ?: 1);

            $rekom->nilai_tl_selesai = $hasLunas ? $nilaiRekom : 0;
            $rekom->nilai_sisa       = $hasLunas ? 0 : $nilaiRekom;
            $rekom->status           = match (true) {
                $hasLunas  => Recommendation::STATUS_SELESAI,
                $hasProses => Recommendation::STATUS_PROSES,
                default    => Recommendation::STATUS_BELUM,
            };
        }

        $rekom->saveQuietly();

        $temuan = $rekom->temuan()->first();
        $temuan?->syncStatus();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeDiterima($query)
    {
        return $query->where('status', self::STATUS_DITERIMA);
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU);
    }

    // public function scopeTelat($query)
    // {
    //     return $query
    //         ->whereNotNull('tanggal_jatuh_tempo_cicilan')
    //         ->whereDate('tanggal_jatuh_tempo_cicilan', '<', now())
    //         ->where('status', '!=', self::STATUS_DITERIMA);
    // }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isDiterima(): bool
    {
        return $this->status === self::STATUS_DITERIMA;
    }

    public function isTelat(): bool
    {
        return $this->tanggal_jatuh_tempo_cicilan?->isPast()
            && $this->status !== self::STATUS_DITERIMA;
    }

    public function isBreakdownValid(): bool
    {
        $sum = (float) ($this->nilai_bayar_negara   ?? 0)
             + (float) ($this->nilai_bayar_daerah   ?? 0)
             + (float) ($this->nilai_bayar_desa     ?? 0)
             + (float) ($this->nilai_bayar_bos_blud ?? 0);

        return $sum === 0.0 || abs($sum - (float) $this->nilai_bayar) < 0.01;
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU => 'Menunggu Verifikasi',
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_DITOLAK  => 'Ditolak',
            default               => $this->status ?? '-',
        };
    }
}