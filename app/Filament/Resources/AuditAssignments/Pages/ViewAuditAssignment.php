<?php

namespace App\Filament\Resources\AuditAssignments\Pages;

use App\Filament\Resources\AuditAssignments\AuditAssignmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditAssignment extends ViewRecord
{
    protected static string $resource = AuditAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
