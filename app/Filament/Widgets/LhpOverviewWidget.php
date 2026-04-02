<?php

namespace App\Filament\Widgets;

use App\Models\Lhp;
use App\Models\Recommendation;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LhpOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            // Menggunakan heroicon-m-clipboard-document-list untuk LHP (Kesan Audit)
            Stat::make('Total LHP', Lhp::count())
                ->description('Laporan Hasil Pemeriksaan')
                ->descriptionIcon('heroicon-m-clipboard-document-list', IconPosition::Before)
                ->chart([5, 8, 5, 12, 10, 15, 12, 18])
                ->color('info'),

            // Menggunakan heroicon-m-check-badge untuk Selesai (Kesan Prestasi)
            Stat::make('Rekomendasi Selesai', Recommendation::where('status', 'selesai')->count())
                ->description('Tuntas ditindaklanjuti')
                ->descriptionIcon('heroicon-m-check-badge', IconPosition::Before)
                ->chart([2, 5, 15, 25, 40, 60, 85, 100])
                ->color('success'),

            // Menggunakan heroicon-m-arrow-path untuk Proses (Kesan Berjalan)
            Stat::make('Rekomendasi Proses', Recommendation::where('status', 'proses')->count())
                ->description('Dalam pemantauan')
                ->descriptionIcon('heroicon-m-arrow-path', IconPosition::Before)
                ->chart([15, 12, 18, 14, 20, 16, 22, 18])
                ->color('warning'),

            // Menggunakan heroicon-m-exclamation-triangle untuk Belum (Kesan Atensi)
            Stat::make('Belum Tindak Lanjut', Recommendation::where('status', 'belum')->count())
                ->description('Memerlukan atensi segera')
                ->descriptionIcon('heroicon-m-exclamation-triangle', IconPosition::Before)
                ->chart([25, 20, 18, 15, 12, 10, 8, 5])
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
