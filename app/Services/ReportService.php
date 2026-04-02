<?php

namespace App\Services;

use App\Models\Lhp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    public function renderOnTheFly(Lhp $lhp)
    {
        $lhp->load(['statistik', 'temuans.kodeTemuan', 'temuans.recommendations.tindakLanjuts.cicilans', 'auditAssignment']);

        return Pdf::loadView('pdf.laporan-lhp', ['record' => $lhp])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false)
            ->stream('LHP-' . str($lhp->nomor_lhp)->slug() . '.pdf');
    }

 

public function renderRekapTabel($ids = null)
    {
        // Ambil data LHP beserta seluruh hirarki ke bawah
        $query = Lhp::with([
            'temuans.kodeTemuan',
            'temuans.recommendations.kodeRekomendasi', // Tambahkan ini
            'temuans.recommendations.tindakLanjuts.cicilans'
        ]);

        if ($ids) {
            $query->whereIn('id', explode(',', $ids));
        }

        $lhps = $query->get();

        $pdf = Pdf::loadView('pdf.laporan-rekap-tabel', [
            'lhps' => $lhps,
        ])
        ->setPaper('a4', 'landscape')
        ->setWarnings(false);

        return $pdf->stream('Rekap-Tindak-Lanjut.pdf');
    }
public function renderAll(\Illuminate\Database\Eloquent\Collection $lhps)
{
    // Eager Load agar cepat
    $lhps->load(['statistik']);

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-rekap-tabel', [
        'lhps' => $lhps,
    ])
    ->setPaper('a4', 'landscape') // Paksa ke Landscape
    ->setWarnings(false);

    return $pdf->stream('Rekapitulasi-LHP.pdf');
}
}