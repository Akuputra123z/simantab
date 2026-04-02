<?php

namespace App\Filament\Resources\Temuans\Pages;

use App\Filament\Resources\Temuans\TemuanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTemuan extends ViewRecord
{
    protected static string $resource = TemuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
