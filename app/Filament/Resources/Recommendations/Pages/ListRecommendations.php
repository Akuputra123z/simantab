<?php

namespace App\Filament\Resources\Recommendations\Pages;

use App\Filament\Resources\Recommendations\RecommendationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecommendations extends ListRecords
{
    protected static string $resource = RecommendationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
