<?php

namespace App\Observers;

use App\Models\TindakLanjut;
use App\Services\LhpStatistikService;

class TindakLanjutObserver
{
    /**
     * Handle the TindakLanjut "saved" event.
     */
    public function saved(TindakLanjut $tindakLanjut): void
    {
        $this->refreshLhpStatistik($tindakLanjut);
    }

    /**
     * Handle the TindakLanjut "deleted" event.
     */
    public function deleted(TindakLanjut $tindakLanjut): void
    {
        $this->refreshLhpStatistik($tindakLanjut);
    }

    /**
     * Helper untuk memicu hitung ulang statistik di LHP terkait.
     */
    protected function refreshLhpStatistik(TindakLanjut $tindakLanjut): void
    {
        // Ambil ID LHP melalui relasi: TindakLanjut -> Recommendation -> Temuan -> Lhp
        $lhpId = $tindakLanjut->recommendation?->temuan?->lhp_id;

        if ($lhpId) {
            // Panggil service melalui app() untuk menghindari error non-static method
            app(LhpStatistikService::class)->updateStatistik($lhpId);
        }
    }
}