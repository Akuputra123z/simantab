<?php

namespace App\Filament\Resources\KodeTemuans\Pages;

use App\Filament\Resources\KodeTemuans\KodeTemuanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKodeTemuan extends EditRecord
{
    protected static string $resource = KodeTemuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
