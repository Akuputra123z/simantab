<?php

namespace App\Filament\Resources\TindakLanjuts\Pages;

use App\Filament\Resources\TindakLanjuts\TindakLanjutResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTindakLanjut extends ViewRecord
{
    protected static string $resource = TindakLanjutResource::class;

    protected function resolveRecord(int|string $key): \Illuminate\Database\Eloquent\Model
    {
        return \App\Models\TindakLanjut::withoutGlobalScopes()
            ->with([
                'recommendation.temuan.lhp',
                'recommendation.temuan.kodeTemuan',
                'recommendation',
                'verifikator',
                'cicilans',
                'attachments',
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
