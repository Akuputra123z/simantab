<?php

namespace App\Filament\Resources\TindakLanjuts;

use App\Filament\Resources\TindakLanjuts\Pages\CreateTindakLanjut;
use App\Filament\Resources\TindakLanjuts\Pages\EditTindakLanjut;
use App\Filament\Resources\TindakLanjuts\Pages\ListTindakLanjuts;
use App\Filament\Resources\TindakLanjuts\Pages\ViewTindakLanjut;
use App\Filament\Resources\TindakLanjuts\RelationManagers\CicilansRelationManager;
use App\Filament\Resources\TindakLanjuts\Schemas\TindakLanjutForm;
use App\Filament\Resources\TindakLanjuts\Schemas\TindakLanjutInfolist;
use App\Filament\Resources\TindakLanjuts\Tables\TindakLanjutsTable;
use App\Models\TindakLanjut;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TindakLanjutResource extends Resource
{
    protected static ?string $model = TindakLanjut::class;
    protected static string | UnitEnum | null $navigationGroup = 'Proses Pemeriksaan';

protected static ?int $navigationSort = 4;


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPathRoundedSquare;

    protected static ?string $recordTitleAttribute = 'TindakLanjut';

    public static function form(Schema $schema): Schema
    {
        return TindakLanjutForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TindakLanjutInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TindakLanjutsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
              CicilansRelationManager::class, 
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => ListTindakLanjuts::route('/'),
            'create' => CreateTindakLanjut::route('/create'),
            'view' => ViewTindakLanjut::route('/{record}'),
            'edit' => EditTindakLanjut::route('/{record}/edit'),
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
