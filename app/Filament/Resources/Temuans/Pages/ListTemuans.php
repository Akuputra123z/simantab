<?php

namespace App\Filament\Resources\Temuans\Pages;

use App\Filament\Resources\Temuans\TemuanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTemuans extends ListRecords
{
    protected static string $resource = TemuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
