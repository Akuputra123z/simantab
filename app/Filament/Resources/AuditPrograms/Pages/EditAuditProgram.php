<?php

namespace App\Filament\Resources\AuditPrograms\Pages;

use App\Filament\Resources\AuditPrograms\AuditProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAuditProgram extends EditRecord
{
    protected static string $resource = AuditProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
