<?php

namespace App\Filament\Resources\AuditPrograms\Pages;

use App\Filament\Resources\AuditPrograms\AuditProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAuditPrograms extends ListRecords
{
    protected static string $resource = AuditProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
