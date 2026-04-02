<?php

namespace App\Filament\Resources\Recommendations\Pages;

use App\Filament\Resources\Recommendations\RecommendationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRecommendation extends ViewRecord
{
    protected static string $resource = RecommendationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
