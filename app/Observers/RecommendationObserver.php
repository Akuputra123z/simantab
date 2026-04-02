<?php

namespace App\Observers;

use App\Models\Recommendation;
use App\Services\LhpStatistikService;

class RecommendationObserver
{
    /**
     * Gunakan Constructor Injection agar Service bisa digunakan kembali.
     */
    public function __construct(
        protected LhpStatistikService $service
    ) {}

    /**
     * Trigger saat status atau nilai rekomendasi berubah.
     */
    public function saved(Recommendation $model): void
    {
        $this->triggerCalculation($model);
    }

    /**
     * Trigger saat rekomendasi dihapus (Soft Delete).
     */
    public function deleted(Recommendation $model): void
    {
        $this->triggerCalculation($model);
    }

    /**
     * Trigger saat data dikembalikan dari Trash (Restore).
     */
    public function restored(Recommendation $model): void
    {
        $this->triggerCalculation($model);
    }

    /**
     * Fungsi pembantu untuk memastikan LHP dihitung ulang.
     */
    protected function triggerCalculation(Recommendation $model): void
    {
        // Gunakan optional chaining atau null check untuk keamanan relasi
        $lhp = $model->temuan?->lhp;

        if ($lhp) {
            $this->service->updateStatistik($lhp->id);
        }
    }
}