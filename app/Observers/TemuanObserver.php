<?php

namespace App\Observers;

use App\Models\Temuan;

class TemuanObserver
{
    public function saved(Temuan $temuan): void
    {
        // ⚠️  Temuan::booted() sudah memanggil refreshStatistik() via LhpStatistikService.
        // Observer ini TIDAK perlu memanggil lagi untuk menghindari double-refresh
        // yang menyebabkan recursive call:
        //   saved → refreshStatistik → event(LhpStatistikUpdated) → SyncLhpReportListener
        //
        // Biarkan booted() yang handle — jangan uncomment kecuali booted() dihapus.
        //
        // $temuan->lhp?->refreshStatistik();
    }

    public function deleted(Temuan $temuan): void
    {
        // Saat delete, booted() tidak terpanggil otomatis untuk 'deleted' event
        // jadi kita perlu refresh di sini
        $temuan->lhp?->refreshStatistik();
    }

    public function restored(Temuan $temuan): void
    {
        $temuan->lhp?->refreshStatistik();
    }
}