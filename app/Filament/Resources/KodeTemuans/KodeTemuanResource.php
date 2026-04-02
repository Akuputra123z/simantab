<?php

namespace App\Filament\Resources\KodeTemuans;

use App\Filament\Resources\KodeTemuans\Pages\CreateKodeTemuan;
use App\Filament\Resources\KodeTemuans\Pages\EditKodeTemuan;
use App\Filament\Resources\KodeTemuans\Pages\ListKodeTemuans;
use App\Filament\Resources\KodeTemuans\Schemas\KodeTemuanForm;
use App\Filament\Resources\KodeTemuans\Tables\KodeTemuansTable;
use App\Models\KodeTemuan;
use UnitEnum;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KodeTemuanResource extends Resource
{
    protected static ?string $model = KodeTemuan::class;
    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $recordTitleAttribute = 'KodeTemuan';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return KodeTemuanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KodeTemuansTable::configure($table);
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
            'index' => ListKodeTemuans::route('/'),
            'create' => CreateKodeTemuan::route('/create'),
            'edit' => EditKodeTemuan::route('/{record}/edit'),
        ];
    }
}
