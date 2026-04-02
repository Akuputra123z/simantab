<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\Temuan;
use Illuminate\Support\Facades\DB;

class RecalculateAllCommand extends Command
{
    protected $signature   = 'inspektorat:recalculate {--lhp= : Hitung ulang hanya untuk LHP ID tertentu}';
    protected $description = 'Hitung ulang semua nilai kalkulasi temuan, rekomendasi, dan tindak lanjut';

    public function handle(): int
    {
        $lhpId = $this->option('lhp');

        $this->info('');
        $this->info('Memulai kalkulasi ulang...');
        $this->info('');

        DB::transaction(function () use ($lhpId) {

            // ── STEP 1: Recommendation ← TindakLanjut ──────────────────────
            $this->info('Step 1/3 — Menghitung Rekomendasi dari Tindak Lanjut...');

            $recommendations = Recommendation::query()
                ->withoutGlobalScopes()
                ->when($lhpId, fn ($q) => $q->whereHas(
                    'temuan', fn ($q) => $q->where('lhp_id', $lhpId)
                ))
                ->with(['tindakLanjuts', 'temuan'])
                ->get();

            $bar = $this->output->createProgressBar($recommendations->count());
            $bar->start();

            foreach ($recommendations as $rekom) {
                $this->recalcRecommendation($rekom);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // ── STEP 2: Temuan ← Recommendation ────────────────────────────
            $this->info('Step 2/3 — Menghitung Temuan dari Rekomendasi...');

            $temuans = Temuan::query()
                ->withoutGlobalScopes()
                ->when($lhpId, fn ($q) => $q->where('lhp_id', $lhpId))
                ->with('recommendations')
                ->get();

            $bar = $this->output->createProgressBar($temuans->count());
            $bar->start();

            foreach ($temuans as $temuan) {
                $this->recalcTemuan($temuan);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // ── STEP 3: LHP ← Temuan ───────────────────────────────────────
            $this->info('Step 3/3 — Menghitung LHP dari Temuan...');

            $lhps = Lhp::query()
                ->withoutGlobalScopes()
                ->when($lhpId, fn ($q) => $q->where('id', $lhpId))
                ->get();

            $bar = $this->output->createProgressBar($lhps->count());
            $bar->start();

            foreach ($lhps as $lhp) {
                $lhp->refreshStatistik();
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        });

        $this->info('');
        $this->info('Kalkulasi selesai!');
        $this->newLine();

        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Total LHP',         Lhp::count()],
                ['Total Temuan',      Temuan::count()],
                ['Temuan Selesai',    Temuan::where('status_tl', Temuan::STATUS_SELESAI)->count()],
                ['Temuan Dalam Proses', Temuan::where('status_tl', Temuan::STATUS_PROSES)->count()],
                ['Temuan Belum',      Temuan::where('status_tl', Temuan::STATUS_BELUM)->count()],
                ['Total Rekomendasi', Recommendation::count()],
                ['Rekom Selesai',     Recommendation::where('status', Recommendation::STATUS_SELESAI)->count()],
                ['Rekom Proses',      Recommendation::where('status', Recommendation::STATUS_PROSES)->count()],
                ['Rekom Belum',       Recommendation::where('status', Recommendation::STATUS_BELUM)->count()],
            ]
        );

        return self::SUCCESS;
    }

    // =========================================================
    // PRIVATE HELPERS — logika kalkulasi ada di sini,
    // tidak perlu method baru di model.
    // =========================================================

    private function recalcRecommendation(Recommendation $rekom): void
    {
        if ($rekom->isUang()) {

            $total = (float) $rekom
                ->tindakLanjuts()
                ->where('status_verifikasi', '!=', 'ditolak')
                ->sum('total_terbayar');

            $nilaiRekom = (float) $rekom->nilai_rekom;

            $status = match (true) {
                $nilaiRekom > 0 && $total >= $nilaiRekom => Recommendation::STATUS_SELESAI,
                $total > 0                               => Recommendation::STATUS_PROSES,
                default                                  => Recommendation::STATUS_BELUM,
            };

            $rekom->updateQuietly([
                'nilai_tl_selesai' => $total,
                'nilai_sisa'       => max(0, $nilaiRekom - $total),
                'status'           => $status,
            ]);

        } else {

            $hasLunas  = $rekom->tindakLanjuts()->where('status_verifikasi', 'lunas')->exists();
            $hasProses = $rekom->tindakLanjuts()
                ->whereIn('status_verifikasi', ['menunggu_verifikasi', 'berjalan'])
                ->exists();

            $nilaiRekom = (float) ($rekom->nilai_rekom ?: 1);

            $rekom->updateQuietly([
                'nilai_tl_selesai' => $hasLunas ? $nilaiRekom : 0,
                'nilai_sisa'       => $hasLunas ? 0 : $nilaiRekom,
                'status'           => match (true) {
                    $hasLunas  => Recommendation::STATUS_SELESAI,
                    $hasProses => Recommendation::STATUS_PROSES,
                    default    => Recommendation::STATUS_BELUM,
                },
            ]);
        }
    }

    private function recalcTemuan(Temuan $temuan): void
    {
        $statuses = $temuan->recommendations()->pluck('status');

        $statusTemuan = match (true) {
            $statuses->isEmpty()
                => Temuan::STATUS_BELUM,

            $statuses->every(fn ($s) => $s === Recommendation::STATUS_SELESAI)
                => Temuan::STATUS_SELESAI,

            $statuses->contains(Recommendation::STATUS_PROSES)
            || $statuses->contains(Recommendation::STATUS_SELESAI)
                => Temuan::STATUS_PROSES,

            default
                => Temuan::STATUS_BELUM,
        };

        $temuan->updateQuietly(['status_tl' => $statusTemuan]);
    }
}