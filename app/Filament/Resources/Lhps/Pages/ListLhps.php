<?php

namespace App\Filament\Resources\Lhps\Pages;

use App\Filament\Resources\Lhps\LhpResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLhps extends ListRecords
{
    protected static string $resource = LhpResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
