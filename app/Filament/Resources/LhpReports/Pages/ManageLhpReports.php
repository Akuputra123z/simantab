<?php

namespace App\Filament\Resources\LhpReports\Pages;

use App\Filament\Resources\LhpReports\LhpReportResource;
// use App\Filament\Widgets\LhpOverviewWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLhpReports extends ManageRecords
{
    protected static string $resource = LhpReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
           Action::make('download_all_pdf')
            ->label('Download PDF')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('danger')
            // Langsung panggil route tanpa params agar ambil semua (Logika di Controller)
            ->url(fn () => route('pdf.laporan-lhp-all'))
            ->openUrlInNewTab(),

            
            Action::make('download_excel')
                ->label('Download Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(fn () => route('excel.rekap-temuan')) // Pastikan route ini sudah dibuat di web.php
                ->openUrlInNewTab(),


        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // LhpOverviewWidget::class, // Gunakan ::class
        ];
    }
}
