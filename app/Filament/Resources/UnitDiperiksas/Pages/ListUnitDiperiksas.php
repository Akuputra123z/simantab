<?php

namespace App\Filament\Resources\UnitDiperiksas\Pages;

use App\Filament\Resources\UnitDiperiksas\UnitDiperiksaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUnitDiperiksas extends ListRecords
{
    protected static string $resource = UnitDiperiksaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
