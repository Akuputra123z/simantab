<?php

namespace App\Filament\Resources\KodeTemuans\Pages;

use App\Filament\Resources\KodeTemuans\KodeTemuanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKodeTemuans extends ListRecords
{
    protected static string $resource = KodeTemuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
