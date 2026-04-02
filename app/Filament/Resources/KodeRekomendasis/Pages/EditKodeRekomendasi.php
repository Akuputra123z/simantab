<?php

namespace App\Filament\Resources\KodeRekomendasis\Pages;

use App\Filament\Resources\KodeRekomendasis\KodeRekomendasiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKodeRekomendasi extends EditRecord
{
    protected static string $resource = KodeRekomendasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
