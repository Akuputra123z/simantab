<?php

namespace App\Filament\Resources\AuditPrograms\Pages;

use App\Filament\Resources\AuditPrograms\AuditProgramResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditProgram extends ViewRecord
{
    protected static string $resource = AuditProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
