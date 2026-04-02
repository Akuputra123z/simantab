<?php

namespace App\Filament\Resources\AuditAssignments\Pages;

use App\Filament\Resources\AuditAssignments\AuditAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAuditAssignments extends ListRecords
{
    protected static string $resource = AuditAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
