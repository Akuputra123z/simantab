<?php

namespace App\Filament\Resources\Recommendations\Pages;

use App\Filament\Resources\Recommendations\RecommendationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRecommendation extends CreateRecord
{
    protected static string $resource = RecommendationResource::class;
}
