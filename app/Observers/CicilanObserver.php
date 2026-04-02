<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\LhpStatistik;
use App\Models\Recommendation;
use App\Models\TindakLanjut;
use App\Models\TindakLanjutCicilan;
use Illuminate\Support\Facades\DB;

/**
 * Saat cicilan berubah → update cache TL → Recommendation → Temuan → LhpStatistik
 *
 * OPTIMASI v2:
 *  1. load relasi dengan select() — hanya kolom yang dibutuhkan
 *  2. cicilans() dihitung dengan 1 query agregat (sum+count sekaligus)
 *  3. recalculateLhpStatistik pakai DB aggregate query langsung, tanpa load collection ke PHP
 *  4. ActivityLog dikirim via queue agar tidak memblokir response user
 */
class CicilanObserver
{
    public function saved(TindakLanjutCicilan $cicilan): void
    {
        // Load relasi minimal yang dibutuhkan sekaligus
        $cicilan->load([
            'tindakLanjut:id,recommendation_id,nilai_bayar',
            'tindakLanjut.recommendation:id,temuan_id,nilai_rekom',
        ]);
        $this->recalculateTindakLanjut($cicilan->tindakLanjut);
    }

    public function deleted(TindakLanjutCicilan $cicilan): void
    {
        $cicilan->load([
            'tindakLanjut:id,recommendation_id',
            'tindakLanjut.recommendation:id,temuan_id,nilai_rekom',
        ]);
        $this->recalculateTindakLanjut($cicilan->tindakLanjut);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function recalculateTindakLanjut(TindakLanjut $tl): void
    {
        $nilaiRekom = (string) $tl->recommendation->nilai_rekom;

        // OPTIMASI: hitung sum + count dalam 1 query, bukan 2 query terpisah
        $agg = DB::table('tindak_lanjut_cicilans')
            ->where('tindak_lanjut_id', $tl->id)
            ->where('status', 'diterima')
            ->whereNull('deleted_at')
            ->selectRaw('SUM(nilai_bayar) as total, COUNT(*) as jumlah')
            ->first();

        $totalTerbayar   = (string) ($agg->total ?? '0');
        $jumlahRealisasi = (int) ($agg->jumlah ?? 0);

        $sisaBelumBayar = bcsub($nilaiRekom, $totalTerbayar, 2);
        if (bccomp($sisaBelumBayar, '0', 2) < 0) {
            $sisaBelumBayar = '0.00';
        }

        $statusVerifikasi = match (true) {
            bccomp($totalTerbayar, '0', 2) <= 0         => 'menunggu_verifikasi',
            bccomp($totalTerbayar, $nilaiRekom, 2) >= 0 => 'lunas',
            default                                      => 'berjalan',
        };

        $tl->updateQuietly([
            'total_terbayar'           => $totalTerbayar,
            'sisa_belum_bayar'         => $sisaBelumBayar,
            'jumlah_cicilan_realisasi' => $jumlahRealisasi,
            'status_verifikasi'        => $statusVerifikasi,
        ]);

        $this->recalculateRecommendation($tl->recommendation);

        // OPTIMASI: ActivityLog via queue — tidak blokir response
        dispatch(function () use ($tl, $totalTerbayar, $sisaBelumBayar, $statusVerifikasi) {
            ActivityLog::record(
                logName: 'cicilan',
                event: 'recalculated',
                description: "Cache TL #{$tl->id} diperbarui: terbayar={$totalTerbayar}, sisa={$sisaBelumBayar}",
                subject: $tl,
                causer: auth()->user(),
                properties: [
                    'total_terbayar'   => $totalTerbayar,
                    'sisa_belum_bayar' => $sisaBelumBayar,
                    'status'           => $statusVerifikasi,
                ]
            );
        })->afterResponse();
    }

    private function recalculateRecommendation(Recommendation $rekom): void
    {
        // OPTIMASI: 1 query sum dari tindak_lanjuts — tidak load collection
        $nilaiTlSelesai = (string) DB::table('tindak_lanjuts')
            ->where('recommendation_id', $rekom->id)
            ->where('status_verifikasi', 'lunas')
            ->whereNull('deleted_at')
            ->sum('total_terbayar');

        $nilaiSisa = bcsub((string) $rekom->nilai_rekom, $nilaiTlSelesai, 2);
        if (bccomp($nilaiSisa, '0', 2) < 0) {
            $nilaiSisa = '0.00';
        }

        $status = match (true) {
            bccomp($nilaiTlSelesai, '0', 2) <= 0                          => 'belum_ditindaklanjuti',
            bccomp($nilaiTlSelesai, (string) $rekom->nilai_rekom, 2) >= 0 => 'selesai',
            default                                                        => 'dalam_proses',
        };

        $rekom->updateQuietly([
            'nilai_tl_selesai' => $nilaiTlSelesai,
            'nilai_sisa'       => $nilaiSisa,
            'status'           => $status,
        ]);

        // OPTIMASI: ambil temuan dengan select minimal
        $this->recalculateTemuan($rekom->temuan_id);
    }

    private function recalculateTemuan(int $temuanId): void
    {
        // OPTIMASI: agregat langsung di DB — tidak load ke PHP collection
        $counts = DB::table('recommendations')
            ->where('temuan_id', $temuanId)
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = 'dalam_proses' THEN 1 ELSE 0 END) as proses
            ")
            ->first();

        $total   = (int) ($counts->total ?? 0);
        $selesai = (int) ($counts->selesai ?? 0);
        $proses  = (int) ($counts->proses ?? 0);

        $statusTl = match (true) {
            $total > 0 && $selesai === $total => 'selesai',
            $proses > 0 || $selesai > 0       => 'dalam_proses',
            default                           => 'belum_ditindaklanjuti',
        };

        DB::table('temuans')
            ->where('id', $temuanId)
            ->update(['status_tl' => $statusTl, 'updated_at' => now()]);

        // Ambil lhp_id
        $lhpId = DB::table('temuans')
            ->where('id', $temuanId)
            ->value('lhp_id');

        if ($lhpId) {
            $this->recalculateLhpStatistik($lhpId);
        }
    }

    private function recalculateLhpStatistik(int $lhpId): void
    {
        // OPTIMASI: semua kalkulasi di DB, bukan di PHP
        // 1 query untuk kerugian per temuan
        $kerugian = DB::table('temuans')
            ->where('lhp_id', $lhpId)
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as total_temuan,
                SUM(nilai_kerugian_negara)   as negara,
                SUM(nilai_kerugian_daerah)   as daerah,
                SUM(nilai_kerugian_desa)     as desa,
                SUM(nilai_kerugian_bos_blud) as bos_blud
            ")
            ->first();

        $totalTemuan         = (int) ($kerugian->total_temuan ?? 0);
        $totalKerugianNegara = (string) ($kerugian->negara   ?? '0');
        $totalKerugianDaerah = (string) ($kerugian->daerah   ?? '0');
        $totalKerugianDesa   = (string) ($kerugian->desa     ?? '0');
        $totalKerugianBos    = (string) ($kerugian->bos_blud ?? '0');
        $totalKerugian       = bcadd(
            bcadd($totalKerugianNegara, $totalKerugianDaerah, 2),
            bcadd($totalKerugianDesa, $totalKerugianBos, 2),
            2
        );

        // 1 query untuk status rekomendasi
        $rekomStats = DB::table('recommendations')
            ->whereIn('temuan_id', function ($q) use ($lhpId) {
                $q->select('id')->from('temuans')
                  ->where('lhp_id', $lhpId)
                  ->whereNull('deleted_at');
            })
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'belum_ditindaklanjuti' THEN 1 ELSE 0 END) as belum,
                SUM(CASE WHEN status = 'dalam_proses' THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(nilai_tl_selesai) as total_tl_selesai
            ")
            ->first();

        $totalRekom   = (int) ($rekomStats->total ?? 0);
        $rekomBelum   = (int) ($rekomStats->belum ?? 0);
        $rekomProses  = (int) ($rekomStats->proses ?? 0);
        $rekomSelesai = (int) ($rekomStats->selesai ?? 0);
        $totalTlSelesai = (string) ($rekomStats->total_tl_selesai ?? '0');
        $totalSisa      = bcsub($totalKerugian, $totalTlSelesai, 2);
        $persen         = $totalRekom > 0
            ? round(($rekomSelesai / $totalRekom) * 100, 2)
            : 0;

        LhpStatistik::updateOrCreate(
            ['lhp_id' => $lhpId],
            [
                'total_temuan'            => $totalTemuan,
                'total_rekomendasi'       => $totalRekom,
                'total_kerugian_negara'   => $totalKerugianNegara,
                'total_kerugian_daerah'   => $totalKerugianDaerah,
                'total_kerugian_desa'     => $totalKerugianDesa,
                'total_kerugian_bos_blud' => $totalKerugianBos,
                'total_kerugian'          => $totalKerugian,
                'total_nilai_tl_selesai'  => $totalTlSelesai,
                'total_sisa_kerugian'     => $totalSisa,
                'rekom_belum'             => $rekomBelum,
                'rekom_proses'            => $rekomProses,
                'rekom_selesai'           => $rekomSelesai,
                'persen_selesai'          => $persen,
                'dihitung_pada'           => now(),
            ]
        );
    }
}
