<?php

namespace App\Filament\Resources\KodeRekomendasis;

use App\Filament\Resources\KodeRekomendasis\Pages\CreateKodeRekomendasi;
use App\Filament\Resources\KodeRekomendasis\Pages\EditKodeRekomendasi;
use App\Filament\Resources\KodeRekomendasis\Pages\ListKodeRekomendasis;
use App\Filament\Resources\KodeRekomendasis\Schemas\KodeRekomendasiForm;
use App\Filament\Resources\KodeRekomendasis\Tables\KodeRekomendasisTable;
use App\Models\KodeRekomendasi;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KodeRekomendasiResource extends Resource
{
    protected static ?string $model = KodeRekomendasi::class;


     protected static string | UnitEnum | null $navigationGroup = 'Master Data';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static ?string $recordTitleAttribute = 'KodeRekomendasi';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return KodeRekomendasiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KodeRekomendasisTable::configure($table);
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
            'index' => ListKodeRekomendasis::route('/'),
            'create' => CreateKodeRekomendasi::route('/create'),
            'edit' => EditKodeRekomendasi::route('/{record}/edit'),
        ];
    }
}
