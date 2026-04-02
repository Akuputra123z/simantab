<?php

use App\Exports\RekapTemuanExport;
use App\Http\Controllers\ReportDownloadController;
use App\Models\Lhp;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;


Route::get('/', function () {
    return view('welcome');
});

/**
 * Route untuk Download SATU LHP (Single)
 * Mengarah ke __invoke di ReportDownloadController
 */
Route::get('/print/lhp/{record}', ReportDownloadController::class)
    ->name('pdf.laporan-lhp');

/**
 * Route untuk Download BANYAK/SEMUA LHP (Bulk)
 * Mengarah ke method bulkDownload di ReportDownloadController
 */
Route::get('/print/lhp-bulk', [ReportDownloadController::class, 'bulkDownload'])
    ->name('pdf.laporan-lhp-all');


Route::get('/export/rekap-temuan-excel', function () {
    $lhps = Lhp::with([
        'temuans.kodeTemuan',
        'auditAssignment.unitDiperiksa',
        'temuans.recommendations.kodeRekomendasi',
        'temuans.recommendations.tindakLanjuts.cicilans'
    ])->get();

    return Excel::download(new RekapTemuanExport($lhps), 'Rekap_Temuan_SIMANTAP_' . date('d-m-Y') . '.xlsx');
})->name('excel.rekap-temuan');