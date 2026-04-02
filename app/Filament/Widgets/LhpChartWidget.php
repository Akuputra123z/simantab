<?php

namespace App\Filament\Widgets;

use App\Models\Recommendation;
use Filament\Widgets\ChartWidget;
use Filament\Support\RawJs;

class LhpChartWidget extends ChartWidget
{
    protected ?string $heading = 'Status Rekomendasi LHP';
    protected static ?int $sort = 2;

    // 🔥 FILTER TAHUN
    protected function getFilters(): ?array
    {
        return Recommendation::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun', 'tahun')
            ->toArray();
    }

    protected function getDefaultFilter(): ?string
    {
        return (string) now()->year;
    }

    protected function getData(): array
    {
        $tahun = $this->filter ?? now()->year;

        $data = Recommendation::selectRaw("
                status,
                COUNT(*) as total
            ")
            ->whereYear('created_at', $tahun)
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [
                [
                    'label' => "Rekomendasi Tahun $tahun",
                    'data' => [
                        $data['selesai'] ?? 0,
                        $data['proses'] ?? 0,
                        $data['belum'] ?? 0,
                    ],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(234, 179, 8, 0.85)',
                        'rgba(239, 68, 68, 0.85)',
                    ],
                    'borderColor' => [
                        '#16a34a',
                        '#ca8a04',
                        '#b91c1c',
                    ],
                    'borderWidth' => 2,
                    'hoverOffset' => 18, // 🔥 efek pop
                ],
            ],
            'labels' => ['Selesai', 'Proses', 'Belum Ditindaklanjuti'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,

            // 🔥 ANIMASI HALUS
            'animation' => [
                'duration' => 1400,
                'easing' => 'easeOutExpo',
            ],

            // 🔥 INTERAKSI
            'hover' => [
                'mode' => 'nearest',
                'intersect' => true,
            ],

            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 16,
                        'font' => [
                            'size' => 13,
                            'weight' => '500',
                        ],
                    ],
                ],

                // 🔥 TOOLTIP MODERN
                'tooltip' => [
                    'backgroundColor' => '#111827',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#e5e7eb',
                    'padding' => 10,
                    'cornerRadius' => 8,
                    'callbacks' => [
                        'label' => new RawJs("
                            function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                var data = context.dataset.data;
                                var total = data.reduce((a, b) => a + b, 0);

                                var percentage = total
                                    ? ((value / total) * 100).toFixed(1)
                                    : 0;

                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        "),
                    ],
                ],
            ],

            // 🔥 DONUT MODERN
            'cutout' => '75%',
        ];
    }
}