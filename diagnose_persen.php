<?php

/**
 * ============================================================
 * DIAGNOSA: persen_selesai = 100% padahal belum selesai
 * ============================================================
 * Jalankan via Tinker:
 *   php artisan tinker
 *   >>> exec(file_get_contents('diagnose_persen.php'));
 *
 * Atau langsung:
 *   php artisan tinker --execute="$(cat diagnose_persen.php)"
 * ============================================================
 */

use App\Models\Lhp;
use App\Models\Recommendation;
use App\Models\TindakLanjut;
use Illuminate\Support\Facades\DB;

$lhpId = 1; // ← ganti jika lhp_id berbeda

$sep   = str_repeat('─', 60);
$sep2  = str_repeat('═', 60);

echo "\n$sep2\n";
echo "  DIAGNOSA LHP ID = $lhpId\n";
echo "$sep2\n\n";

// ── 1. Cek lhp_statistik saat ini ────────────────────────────────────────────
echo "❶  LHP_STATISTIK (cache saat ini)\n$sep\n";
$stat = DB::table('lhp_statistik')->where('lhp_id', $lhpId)->first();
if (!$stat) {
    echo "  [!] Tidak ditemukan row di lhp_statistik untuk lhp_id=$lhpId\n\n";
} else {
    echo sprintf("  total_rekomendasi      = %d\n",   $stat->total_rekomendasi);
    echo sprintf("  rekom_selesai          = %d\n",   $stat->rekom_selesai);
    echo sprintf("  rekom_proses           = %d\n",   $stat->rekom_proses);
    echo sprintf("  rekom_belum            = %d\n",   $stat->rekom_belum);
    echo sprintf("  rekom_uang_total       = %d\n",   $stat->rekom_uang_total);
    echo sprintf("  rekom_uang_selesai     = %d\n",   $stat->rekom_uang_selesai);
    echo sprintf("  rekom_nonutang_total   = %d\n",   $stat->rekom_nonutang_total);
    echo sprintf("  rekom_nonutang_selesai = %d\n",   $stat->rekom_nonutang_selesai);
    echo sprintf("  persen_selesai         = %s\n",   $stat->persen_selesai);
    echo sprintf("  persen_selesai_nilai   = %s\n",   $stat->persen_selesai_nilai   ?? 'NULL');
    echo sprintf("  persen_selesai_gabungan= %s\n",   $stat->persen_selesai_gabungan ?? 'NULL');
    echo sprintf("  dihitung_pada          = %s\n\n", $stat->dihitung_pada ?? 'NULL');
}

// ── 2. Cek semua rekomendasi (termasuk soft-deleted) ─────────────────────────
echo "❷  REKOMENDASI (semua, termasuk soft-deleted)\n$sep\n";
$rekoms = DB::table('recommendations')
    ->whereIn('temuan_id', function ($q) use ($lhpId) {
        $q->select('id')->from('temuans')
          ->where('lhp_id', $lhpId)
          ->whereNull('deleted_at');
    })
    ->select('id', 'status', 'jenis_rekomendasi', 'nilai_rekom',
             'nilai_tl_selesai', 'deleted_at')
    ->get();

foreach ($rekoms as $r) {
    $del = $r->deleted_at ? " [SOFT-DELETED: {$r->deleted_at}]" : '';
    echo sprintf(
        "  id=%-3d | %-26s | %-14s | rekom=%12s | tl_selesai=%12s%s\n",
        $r->id,
        $r->status,
        $r->jenis_rekomendasi,
        number_format($r->nilai_rekom, 2),
        number_format($r->nilai_tl_selesai, 2),
        $del
    );
}

$aktif   = $rekoms->whereNull('deleted_at');
$deleted = $rekoms->whereNotNull('deleted_at');
echo sprintf("\n  Total: %d | Aktif: %d | Soft-deleted: %d\n\n",
    $rekoms->count(), $aktif->count(), $deleted->count());

// ── 3. Cek TindakLanjut untuk setiap rekom aktif ─────────────────────────────
echo "❸  TINDAK LANJUT per rekomendasi aktif\n$sep\n";
foreach ($aktif as $r) {
    $tls = DB::table('tindak_lanjuts')
        ->where('recommendation_id', $r->id)
        ->select('id', 'is_cicilan', 'status_verifikasi',
                 'nilai_tindak_lanjut', 'total_terbayar', 'deleted_at')
        ->get();

    echo sprintf("  Rekom id=%-3d (%s / %s):\n", $r->id, $r->jenis_rekomendasi, $r->status);

    if ($tls->isEmpty()) {
        echo "    → Tidak ada TindakLanjut\n";
    } else {
        foreach ($tls as $tl) {
            $del    = $tl->deleted_at ? " [SOFT-DELETED]" : '';
            $cicil  = $tl->is_cicilan ? 'cicilan' : 'langsung';
            echo sprintf(
                "    TL id=%-3d | %s | verifikasi=%-25s | total_terbayar=%s%s\n",
                $tl->id,
                $cicil,
                $tl->status_verifikasi,
                number_format($tl->total_terbayar, 2),
                $del
            );
        }
    }
    echo "\n";
}

// ── 4. Simulasi ulang refreshStatistik() ─────────────────────────────────────
echo "❹  SIMULASI refreshStatistik() (tidak mengubah DB)\n$sep\n";

$rekomAgg = DB::table('recommendations')
    ->whereIn('temuan_id', function ($q) use ($lhpId) {
        $q->select('id')->from('temuans')
          ->where('lhp_id', $lhpId)
          ->whereNull('deleted_at');
    })
    ->whereNull('deleted_at')
    ->selectRaw("
        COUNT(*) as total_rekom,
        SUM(CASE WHEN status = 'selesai'               THEN 1 ELSE 0 END) as rekom_selesai,
        SUM(CASE WHEN status = 'dalam_proses'          THEN 1 ELSE 0 END) as rekom_proses,
        SUM(CASE WHEN status = 'belum_ditindaklanjuti' THEN 1 ELSE 0 END) as rekom_belum,
        SUM(CASE WHEN jenis_rekomendasi = 'uang' THEN 1 ELSE 0 END)                              as rekom_uang_total,
        SUM(CASE WHEN jenis_rekomendasi = 'uang' AND status = 'selesai' THEN 1 ELSE 0 END)       as rekom_uang_selesai,
        SUM(CASE WHEN jenis_rekomendasi != 'uang' THEN 1 ELSE 0 END)                             as rekom_nonutang_total,
        SUM(CASE WHEN jenis_rekomendasi != 'uang' AND status = 'selesai' THEN 1 ELSE 0 END)      as rekom_nonutang_selesai,
        COALESCE(SUM(CASE WHEN jenis_rekomendasi = 'uang' THEN nilai_rekom      ELSE 0 END), 0)  as total_nilai_rekom_uang,
        COALESCE(SUM(CASE WHEN jenis_rekomendasi = 'uang' THEN nilai_tl_selesai ELSE 0 END), 0)  as total_tl_selesai_uang
    ")
    ->first();

$totalRekom        = (int) $rekomAgg->total_rekom;
$rekomSelesai      = (int) $rekomAgg->rekom_selesai;
$rekomUangTotal    = (int) $rekomAgg->rekom_uang_total;
$rekomNonUangTotal = (int) $rekomAgg->rekom_nonutang_total;
$nilaiRekomUang    = (float) $rekomAgg->total_nilai_rekom_uang;
$nilaiSelesaiUang  = (float) $rekomAgg->total_tl_selesai_uang;

$persenCount = $totalRekom > 0 ? round($rekomSelesai / $totalRekom * 100, 2) : 0.0;
$persenNilai = $nilaiRekomUang > 0 ? round($nilaiSelesaiUang / $nilaiRekomUang * 100, 2) : 0.0;

if ($totalRekom === 0) {
    $persenGabungan = 0.0;
    $kasusGabungan  = 'Total rekom = 0';
} elseif ($rekomUangTotal === 0) {
    $persenGabungan = $persenCount;
    $kasusGabungan  = 'Semua NON-UANG → pakai count';
} elseif ($rekomNonUangTotal === 0) {
    $persenGabungan = $persenNilai;
    $kasusGabungan  = 'Semua UANG → pakai nilai rupiah';
} else {
    $bobotUang    = $rekomUangTotal    / $totalRekom;
    $bobotNonUang = $rekomNonUangTotal / $totalRekom;
    $progressNonUang = round((int) $rekomAgg->rekom_nonutang_selesai / $rekomNonUangTotal * 100, 2);
    $persenGabungan  = round(($bobotUang * $persenNilai) + ($bobotNonUang * $progressNonUang), 2);
    $kasusGabungan   = "CAMPURAN → bobot uang={$bobotUang}, nonuang={$bobotNonUang}";
}

echo sprintf("  Kasus gabungan    : %s\n", $kasusGabungan);
echo sprintf("  persen_count      : %.2f%%\n", $persenCount);
echo sprintf("  persen_nilai      : %.2f%%\n", $persenNilai);
echo sprintf("  persen_gabungan   : %.2f%%\n\n", $persenGabungan);

// ── 5. Deteksi penyebab ───────────────────────────────────────────────────────
echo "❺  DETEKSI MASALAH\n$sep\n";

$masalahDitemukan = false;

// 5a: Rekom non-uang status selesai tapi tidak ada TL lunas
foreach ($aktif->where('jenis_rekomendasi', '!=', 'uang') as $r) {
    if ($r->status === 'selesai') {
        $hasLunas = DB::table('tindak_lanjuts')
            ->where('recommendation_id', $r->id)
            ->whereNull('deleted_at')
            ->where('status_verifikasi', 'lunas')
            ->exists();
        if (!$hasLunas) {
            echo "  ⚠️  Rekom id={$r->id} ({$r->jenis_rekomendasi}) status='selesai'\n";
            echo "      tapi TIDAK ADA TindakLanjut aktif dengan status_verifikasi='lunas'!\n";
            echo "      → STATUS REKOM TIDAK VALID / DATA KORUP\n\n";
            $masalahDitemukan = true;
        }
    }
}

// 5b: Statistik tidak sinkron dengan data aktual
if ($stat) {
    if ((int)$stat->rekom_selesai !== $rekomSelesai) {
        echo "  ⚠️  CACHE STALE: rekom_selesai di statistik={$stat->rekom_selesai} ";
        echo "tapi aktual=$rekomSelesai\n";
        echo "      → Perlu panggil \$lhp->refreshStatistik()\n\n";
        $masalahDitemukan = true;
    }
    if ((int)$stat->rekom_nonutang_selesai !== (int)$rekomAgg->rekom_nonutang_selesai) {
        $aktualNon = (int)$rekomAgg->rekom_nonutang_selesai;
        echo "  ⚠️  CACHE STALE: rekom_nonutang_selesai di statistik={$stat->rekom_nonutang_selesai} ";
        echo "tapi aktual=$aktualNon\n\n";
        $masalahDitemukan = true;
    }
    $selisihPersen = abs((float)$stat->persen_selesai_gabungan - $persenGabungan);
    if ($selisihPersen > 0.1) {
        echo "  ⚠️  PERSEN TIDAK SINKRON: statistik={$stat->persen_selesai_gabungan}%";
        echo " tapi kalkulasi ulang=$persenGabungan%\n\n";
        $masalahDitemukan = true;
    }
}

// 5c: Cek TindakLanjut barang yang punya soft-deleted TL lunas
foreach ($aktif->where('jenis_rekomendasi', '!=', 'uang') as $r) {
    $tlLunasDeleted = DB::table('tindak_lanjuts')
        ->where('recommendation_id', $r->id)
        ->whereNotNull('deleted_at')
        ->where('status_verifikasi', 'lunas')
        ->count();
    if ($tlLunasDeleted > 0) {
        echo "  ⚠️  Rekom id={$r->id}: ada $tlLunasDeleted TL lunas yang SUDAH DI-DELETE\n";
        echo "      tapi rekom->status masih '{$r->status}'\n";
        echo "      → Soft-delete TL tidak mentrigger re-evaluasi status rekom\n\n";
        $masalahDitemukan = true;
    }
}

if (!$masalahDitemukan) {
    echo "  ✅ Tidak ditemukan masalah yang jelas. Coba jalankan refreshStatistik() manual:\n";
    echo "     App\\Models\\Lhp::find($lhpId)->refreshStatistik();\n\n";
} else {
    echo "  ─── SARAN PERBAIKAN ───\n";
    echo "  Jalankan di Tinker untuk reset data:\n\n";
    echo "  // 1. Paksa evaluasi ulang semua status rekom non-uang di LHP ini\n";
    echo "  \$lhp = App\\Models\\Lhp::find($lhpId);\n";
    echo "  \$lhp->recommendations()->where('jenis_rekomendasi','!=','uang')\n";
    echo "       ->each(function(\$r) {\n";
    echo "           \$hasLunas = \$r->tindakLanjuts()->whereNull('deleted_at')\n";
    echo "                          ->where('status_verifikasi','lunas')->exists();\n";
    echo "           \$hasProses = \$r->tindakLanjuts()->whereNull('deleted_at')\n";
    echo "                           ->whereIn('status_verifikasi',['menunggu_verifikasi','berjalan'])->exists();\n";
    echo "           \$status = \$hasLunas ? 'selesai' : (\$hasProses ? 'dalam_proses' : 'belum_ditindaklanjuti');\n";
    echo "           \$r->updateQuietly(['status' => \$status]);\n";
    echo "           echo \"Rekom {$r->id} -> \$status\\n\";\n";
    echo "       });\n\n";
    echo "  // 2. Refresh statistik\n";
    echo "  \$lhp->refreshStatistik();\n";
    echo "  echo 'persen_selesai_gabungan = ' . \$lhp->fresh()->statistik->persen_selesai_gabungan;\n\n";
}

echo "$sep2\n  SELESAI\n$sep2\n\n";
