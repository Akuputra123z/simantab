<?php

namespace App\Filament\Resources\UnitDiperiksas;

use App\Filament\Resources\UnitDiperiksas\Pages\CreateUnitDiperiksa;
use App\Filament\Resources\UnitDiperiksas\Pages\EditUnitDiperiksa;
use App\Filament\Resources\UnitDiperiksas\Pages\ListUnitDiperiksas;
use App\Filament\Resources\UnitDiperiksas\Schemas\UnitDiperiksaForm;
use App\Filament\Resources\UnitDiperiksas\Tables\UnitDiperiksasTable;
use App\Models\UnitDiperiksa;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitDiperiksaResource extends Resource
{
    protected static ?string $model = UnitDiperiksa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'UnitDiperiksa';
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return UnitDiperiksaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitDiperiksasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnitDiperiksas::route('/'),
            'create' => CreateUnitDiperiksa::route('/create'),
            'edit' => EditUnitDiperiksa::route('/{record}/edit'),
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
