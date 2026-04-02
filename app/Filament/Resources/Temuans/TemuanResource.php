<?php

namespace App\Filament\Resources\Temuans;

use App\Filament\Resources\Temuans\Pages\CreateTemuan;
use App\Filament\Resources\Temuans\Pages\EditTemuan;
use App\Filament\Resources\Temuans\Pages\ListTemuans;
use App\Filament\Resources\Temuans\Pages\ViewTemuan;
use App\Filament\Resources\Temuans\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\Temuans\Schemas\TemuanForm;
use App\Filament\Resources\Temuans\Schemas\TemuanInfolist;
use App\Filament\Resources\Temuans\Tables\TemuansTable;
use App\Models\Temuan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TemuanResource extends Resource
{
    protected static ?string $model = Temuan::class;
    protected static string | UnitEnum | null $navigationGroup = 'Pemeriksaan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $recordTitleAttribute = 'Temuan';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Temuan Pemeriksaan';
    }


    public static function form(Schema $schema): Schema
    {
        return TemuanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TemuanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TemuansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
              RelationManagers\RecommendationsRelationManager::class,
        ];
    }

   public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = Auth::user();

    if ($user->hasRole('super_admin')) {
        return $query;
    }

    // Pastikan foreign key di relasi LHP menggunakan 'id', bukan 'lhp_id'
    return $query->whereHas('lhp.auditAssignment', function ($q) use ($user) {
        $q->where(function ($sub) use ($user) {
            $sub->where('ketua_tim_id', $user->id)
                ->orWhereHas('members', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id);
                });
        });
    });
}

  

    public static function getPages(): array
    {
        return [
            'index' => ListTemuans::route('/'),
            'create' => CreateTemuan::route('/create'),
            'view' => ViewTemuan::route('/{record}'),
            'edit' => EditTemuan::route('/{record}/edit'),
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
