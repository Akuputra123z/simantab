<?php

namespace App\Services;

use App\Models\Lhp;
use App\Models\LhpStatistik;
use Illuminate\Support\Facades\DB;

class AuditStatistikService
{
    public function updateStatistik(int $lhpId): void
    {
        DB::transaction(function () use ($lhpId) {
            // Load LHP beserta semua relasinya
            $lhp = Lhp::with(['temuans.recommendations'])->find($lhpId);
            
            if (!$lhp) return;

            $allRekom = $lhp->temuans->flatMap->recommendations;
            $totalRekom = $allRekom->count();

            if ($totalRekom === 0) {
                $this->resetStatistik($lhpId);
                return;
            }

            // 1. Tentukan bobot per item (Misal: 4 item = 25% per item)
            $bobotPerItem = 100 / $totalRekom;
            $totalProgressLhp = 0;

            foreach ($allRekom as $rekom) {
                if ($rekom->jenis_rekomendasi === 'uang') {
                    // Perhitungan Proporsional (Sesuai Cicilan)
                    // Rumus: (Uang Masuk / Total Tagihan) * Bobot Item
                    $progresUang = ($rekom->nilai_rekom > 0) 
                        ? ($rekom->nilai_tl_selesai / $rekom->nilai_rekom) * $bobotPerItem 
                        : 0;
                    
                    // Pastikan tidak melebihi bobot item (karena pembulatan atau overpayment)
                    $totalProgressLhp += min($progresUang, $bobotPerItem);
                } else {
                    // Perhitungan Administratif/Barang (Diskrit)
                    // Hanya naik jika statusnya 'selesai'
                    if ($rekom->status === 'selesai') {
                        $totalProgressLhp += $bobotPerItem;
                    }
                }
            }

            // 2. Simpan ke tabel cache statistik agar Load Optimize
            LhpStatistik::updateOrCreate(
                ['lhp_id' => $lhpId],
                [
                    'total_temuan' => $lhp->temuans->count(),
                    'total_rekomendasi' => $totalRekom,
                    'persen_selesai_gabungan' => round($totalProgressLhp, 2),
                    'dihitung_pada' => now(),
                ]
            );
        });
    }

    private function resetStatistik(int $lhpId): void
    {
        LhpStatistik::updateOrCreate(['lhp_id' => $lhpId], [
            'total_temuan' => 0,
            'total_rekomendasi' => 0,
            'persen_selesai_gabungan' => 0,
            'dihitung_pada' => now(),
        ]);
    }
}