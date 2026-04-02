<?php

namespace App\Http\Controllers;

use App\Exports\RekapTemuanExport;
use App\Models\Lhp;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportDownloadController extends Controller
{
    // Single Download
    public function __invoke(Lhp $record, ReportService $service)
    {
        if ($record->getPersenSelesaiAttribute() < 100) {
            abort(403, "Laporan belum selesai 100%.");
        }
        return $service->renderOnTheFly($record);
    }

    // Bulk/All Download

public function bulkDownload(Request $request, ReportService $service)
{
    $ids = $request->query('ids');
    
    // Ambil data berdasarkan ID atau ambil semua jika parameter kosong
    $query = $ids ? \App\Models\Lhp::whereIn('id', explode(',', $ids)) : \App\Models\Lhp::query();

    $records = $query->with('statistik')->get();

    if ($records->isEmpty()) {
        abort(404, "Data LHP tidak ditemukan.");
    }

    // Panggil renderAll yang sudah kita set ke Landscape & Rekap Tabel
    return $service->renderAll($records);
}
public function exportExcel()
{
    $lhps = Lhp::with([
        'temuans.kodeTemuan',
        'auditAssignment.unitDiperiksa',
        'temuans.recommendations.kodeRekomendasi',
        'temuans.recommendations.tindakLanjuts.cicilans'
    ])->get();

    return Excel::download(new RekapTemuanExport($lhps), 'Rekap_Temuan_SIMANTAP.xlsx');
}
}