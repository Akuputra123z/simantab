<?php

namespace App\Filament\Resources\UnitDiperiksas\Pages;

use App\Filament\Resources\UnitDiperiksas\UnitDiperiksaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUnitDiperiksa extends EditRecord
{
    protected static string $resource = UnitDiperiksaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
