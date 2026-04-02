<?php

namespace App\Filament\Widgets;

use App\Models\Lhp;
use Filament\Widgets\ChartWidget;
use Filament\Forms\Components\Select;
use Illuminate\Support\Carbon;

class LhpYoYChartWidget extends ChartWidget
{
    protected ?string $heading = 'LHP Year-over-Year';
    protected static ?int $sort = 1;

    public ?string $tahun = null;
    public ?string $irban = null;
    public ?int $semester = null;

    // 🔥 FILTER FORM
    protected function getFormSchema(): array
    {
        return [
            Select::make('tahun')
                ->label('Tahun')
                ->options(
                    Lhp::selectRaw('YEAR(tanggal_lhp) as tahun')
                        ->distinct()
                        ->orderBy('tahun', 'desc')
                        ->pluck('tahun', 'tahun')
                        ->toArray()
                )
                ->default(now()->year)
                ->reactive(),

            Select::make('irban')
                ->label('IRBAN')
                ->options(
                    Lhp::select('irban')
                        ->distinct()
                        ->pluck('irban', 'irban')
                        ->toArray()
                )
                ->placeholder('Semua')
                ->reactive(),

            Select::make('semester')
                ->label('Semester')
                ->options([
                    1 => 'Semester 1',
                    2 => 'Semester 2',
                ])
                ->placeholder('Semua')
                ->reactive(),
        ];
    }

    protected function getData(): array
    {
        $months = collect(range(1, 12));

        $currentYear = $this->tahun ?? now()->year;
        $lastYear = $currentYear - 1;

        // 🔥 QUERY DINAMIS (pakai when biar clean)
        $currentData = $months->map(function ($month) use ($currentYear) {
            return Lhp::when($this->irban, fn ($q) => $q->where('irban', $this->irban))
                ->when($this->semester, fn ($q) => $q->where('semester', $this->semester))
                ->whereYear('tanggal_lhp', $currentYear)
                ->whereMonth('tanggal_lhp', $month)
                ->count();
        });

        $lastData = $months->map(function ($month) use ($lastYear) {
            return Lhp::when($this->irban, fn ($q) => $q->where('irban', $this->irban))
                ->when($this->semester, fn ($q) => $q->where('semester', $this->semester))
                ->whereYear('tanggal_lhp', $lastYear)
                ->whereMonth('tanggal_lhp', $month)
                ->count();
        });

        $labels = $months->map(fn ($m) => Carbon::create()->month($m)->format('M'));

        return [
            'datasets' => [
                [
                    'label' => "Tahun $currentYear",
                    'data' => $currentData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => "Tahun $lastYear",
                    'data' => $lastData,
                    'borderColor' => '#94a3b8',
                    'borderDash' => [5, 5],
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}