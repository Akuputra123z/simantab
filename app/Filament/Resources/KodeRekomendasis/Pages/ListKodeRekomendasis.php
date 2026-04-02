<?php

namespace App\Filament\Resources\KodeRekomendasis\Pages;

use App\Filament\Resources\KodeRekomendasis\KodeRekomendasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKodeRekomendasis extends ListRecords
{
    protected static string $resource = KodeRekomendasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
