<?php

namespace App\Filament\Resources\TindakLanjuts\Pages;

use App\Filament\Resources\TindakLanjuts\TindakLanjutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTindakLanjuts extends ListRecords
{
    protected static string $resource = TindakLanjutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
