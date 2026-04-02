<?php

namespace App\Filament\Resources\Recommendations;

use App\Filament\Resources\Recommendations\Pages\CreateRecommendation;
use App\Filament\Resources\Recommendations\Pages\EditRecommendation;
use App\Filament\Resources\Recommendations\Pages\ListRecommendations;
use App\Filament\Resources\Recommendations\Pages\ViewRecommendation;
use App\Filament\Resources\Recommendations\Schemas\RecommendationForm;
use App\Filament\Resources\Recommendations\Schemas\RecommendationInfolist;
use App\Filament\Resources\Recommendations\Tables\RecommendationsTable;
use App\Models\Recommendation;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecommendationResource extends Resource
{
    protected static ?string $model = Recommendation::class;
    protected static string | UnitEnum | null $navigationGroup = 'Proses Pemeriksaan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static ?string $recordTitleAttribute = 'Recommendation';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return RecommendationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RecommendationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecommendationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
             RelationManagers\TindakLanjutsRelationManager::class, 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecommendations::route('/'),
            'create' => CreateRecommendation::route('/create'),
            'view' => ViewRecommendation::route('/{record}'),
            'edit' => EditRecommendation::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
