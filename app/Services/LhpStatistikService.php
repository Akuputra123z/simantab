<?php

namespace App\Services;

use App\Models\Lhp;
use App\Models\LhpStatistik;
use App\Events\LhpStatistikUpdated;

class LhpStatistikService
{
    public function hitung($lhp): void
    {
        $lhpId = $lhp instanceof Lhp ? $lhp->id : $lhp;
        $this->updateStatistik($lhpId);
    }

public function updateStatistik(int $lhpId): void
{
    $lhp = Lhp::with([
        'temuans.recommendations.tindakLanjuts.cicilans' // ✅ tambah cicilans
    ])->find($lhpId);

    if (! $lhp) return;

    $allRekom   = $lhp->temuans->flatMap->recommendations;
    $totalRekom = $allRekom->count();

    if ($totalRekom === 0) {
        $this->resetStatistik($lhpId);
        event(new LhpStatistikUpdated($lhp));
        return;
    }

    $bobotPerItem  = 100 / $totalRekom;
    $totalProgress = 0;

    foreach ($allRekom as $rekom) {
        $tls        = $rekom->tindakLanjuts;
        $nilaiRekom = (float) ($rekom->nilai_rekom ?? 0);

        if ($rekom->isUang()) {
            $nilaiLunas = 0;

            foreach ($tls as $tl) {
                if ($tl->jenis_penyelesaian === 'cicilan') {
                    // ✅ Hitung langsung dari cicilans yang sudah di-load
                    $nilaiLunas += (float) $tl->cicilans
                        ->where('status', 'diterima')
                        ->sum('nilai_bayar');
                } elseif ($tl->status_verifikasi === 'lunas') {
                    $nilaiLunas += (float) ($tl->nilai_tindak_lanjut ?? 0);
                }
            }

            if ($nilaiRekom > 0) {
                $persen        = min($nilaiLunas / $nilaiRekom, 1.0);
                $totalProgress += $persen * $bobotPerItem;
            }

        } else {
            $hasLunas      = $tls->where('status_verifikasi', 'lunas')->isNotEmpty();
            $totalProgress += $hasLunas ? $bobotPerItem : 0;
        }
    }

    // ✅ Gunakan konstanta atau string yang konsisten
    $rekomSelesai = $allRekom->where('status', 'selesai')->count();
    $rekomProses  = $allRekom->whereIn('status', ['proses', 'dalam_proses'])->count();
    $rekomBelum   = $totalRekom - $rekomSelesai - $rekomProses; // ✅ sisa = belum

    $totalKerugian = (float) $lhp->temuans->sum(fn ($t) =>
        (float) $t->nilai_kerugian_negara +
        (float) $t->nilai_kerugian_daerah +
        (float) $t->nilai_kerugian_desa   +
        (float) $t->nilai_kerugian_bos_blud
    );

    LhpStatistik::updateOrCreate(
        ['lhp_id' => $lhpId],
        [
            'total_temuan'            => $lhp->temuans->count(),
            'total_rekomendasi'       => $totalRekom,
            'rekom_selesai'           => $rekomSelesai,
            'rekom_proses'            => $rekomProses,
            'rekom_belum'             => $rekomBelum, // ✅ fix
            'total_kerugian'          => $totalKerugian,
            'total_nilai_tl_selesai'  => (float) $allRekom->sum('nilai_tl_selesai'),
            'persen_selesai_gabungan' => round(min($totalProgress, 100), 2),
            'dihitung_pada'           => now(),
        ]
    );

    event(new LhpStatistikUpdated($lhp->fresh()));
}

    private function resetStatistik(int $lhpId): void
    {
        LhpStatistik::updateOrCreate(['lhp_id' => $lhpId], [
            'total_temuan'            => 0,
            'total_rekomendasi'       => 0,
            'rekom_selesai'           => 0,
            'rekom_proses'            => 0,
            'rekom_belum'             => 0,
            'total_kerugian'          => 0,
            'total_nilai_tl_selesai'  => 0,
            'persen_selesai_gabungan' => 0,
            'dihitung_pada'           => now(),
        ]);
    }
}