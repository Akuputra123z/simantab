<?php

namespace App\Filament\Resources\Lhps\Pages;

use App\Filament\Resources\Lhps\LhpResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class LaporanAudit extends Page
{
    use InteractsWithRecord;

    protected static string $resource = LhpResource::class;

    protected string $view = 'filament.resources.lhps.pages.laporan-audit';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
