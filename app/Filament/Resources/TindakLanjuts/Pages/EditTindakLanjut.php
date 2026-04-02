<?php

namespace App\Filament\Resources\TindakLanjuts\Pages;

use App\Filament\Resources\TindakLanjuts\TindakLanjutResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\EditRecord;

class EditTindakLanjut extends EditRecord
{
    protected static string $resource = TindakLanjutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            // EditAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
