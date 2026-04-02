<?php

namespace App\Filament\Resources\Lhps\Pages;

use App\Filament\Resources\Lhps\LhpResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLhp extends ViewRecord
{
    protected static string $resource = LhpResource::class;


    protected function resolveRecord(int | string $key): \Illuminate\Database\Eloquent\Model
    {
        return \App\Models\Lhp::withoutGlobalScopes()
            ->with([
                'auditAssignment.auditProgram',
                'auditAssignment.unitDiperiksa',
                'auditAssignment.ketuaTim',
                'statistik',
                'attachments',           // ← lampiran LHP
            ])
            ->findOrFail($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
