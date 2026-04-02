<?php

namespace App\Observers;

use App\Models\TindakLanjutCicilan;
use App\Services\AuditStatistikService;

class TindakLanjutCicilanObserver
{
    public function __construct(protected AuditStatistikService $service) {}

    public function saved(TindakLanjutCicilan $cicilan): void
    {
        // Ambil LHP ID melalui relasi: Cicilan -> TL -> Rekom -> Temuan -> LHP
        $lhpId = $cicilan->tindakLanjut->recommendation->temuan->lhp_id;
        $this->service->updateStatistik($lhpId);
    }

    public function deleted(TindakLanjutCicilan $cicilan): void
    {
        $lhpId = $cicilan->tindakLanjut->recommendation->temuan->lhp_id;
        $this->service->updateStatistik($lhpId);
    }
}