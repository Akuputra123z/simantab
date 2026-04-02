<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\Lhp;
use App\Models\LhpReport;
use App\Models\Recommendation;
use App\Models\Temuan;
use App\Models\TindakLanjut;
use App\Models\TindakLanjutCicilan;
use App\Models\User;
use App\Observers\AttachmentObserver;
use App\Observers\RecommendationObserver;
use App\Observers\TindakLanjutCicilanObserver;
use App\Observers\TindakLanjutObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Morph Map ────────────────────────────────────────────────────────
        // Penting untuk relasi polymorphic (seperti Attachment)
        Relation::morphMap([
            'lhp'            => Lhp::class,
            'temuan'         => Temuan::class,
            'recommendation' => Recommendation::class,
            'tindak_lanjut'  => TindakLanjut::class,
            'cicilan'        => TindakLanjutCicilan::class,
            'lhp_report'     => LhpReport::class,
            'user'           => User::class,
        ]);

        // ── Observers ────────────────────────────────────────────────────────
        // Urutan ini memastikan setiap perubahan data di level bawah 
        // akan memicu hitung ulang statistik sampai ke level LHP.
        
        Recommendation::observe(RecommendationObserver::class);
        TindakLanjut::observe(TindakLanjutObserver::class);
        TindakLanjutCicilan::observe(TindakLanjutCicilanObserver::class);
        Attachment::observe(AttachmentObserver::class);


        Temuan::observe(\App\Observers\TemuanObserver::class);
    }
}