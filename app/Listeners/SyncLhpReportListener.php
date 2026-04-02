<?php

namespace App\Listeners;

use App\Events\LhpStatistikUpdated;
use App\Models\LhpReport;
use App\Services\ReportService;
use Illuminate\Support\Facades\Log;

class SyncLhpReportListener
{
    public function handle(LhpStatistikUpdated $event): void
    {
        $lhp = $event->lhp;

        // Cek apakah LHP memenuhi syarat laporan
        $memenuhi = $lhp->status === 'final' || $lhp->status === 'ditandatangani';
        $progress = $lhp->statistik?->persen_selesai_gabungan ?? 0;

        if (! $memenuhi || $progress < 100) {
            return;
        }

        try {
            app(ReportService::class)->generate($lhp, 'laporan_akhir');
        } catch (\Throwable $e) {
            Log::error('Gagal sync report: ' . $e->getMessage(), [
                'lhp_id' => $lhp->id,
            ]);
        }
    }
}