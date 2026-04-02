<?php

namespace App\Filament\Resources\Lhps;

use App\Filament\Resources\Lhps\Pages\CreateLhp;
use App\Filament\Resources\Lhps\Pages\EditLhp;
use App\Filament\Resources\Lhps\Pages\ListLhps;
use App\Filament\Resources\Lhps\Pages\ViewLhp;
use App\Filament\Resources\Lhps\Schemas\LhpForm;
use App\Filament\Resources\Lhps\Schemas\LhpInfolist;
use App\Filament\Resources\Lhps\Tables\LhpsTable;
use App\Models\Lhp;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LhpResource extends Resource
{
    protected static ?string $model = Lhp::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static string | UnitEnum | null $navigationGroup = 'Pemeriksaan';

    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'nomor_lhp';

    public static function getNavigationLabel(): string
    {
        return 'LHP';
    }

    public static function form(Schema $schema): Schema
    {
        return LhpForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LhpInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LhpsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TemuansRelationManager::class,
            RelationManagers\ReportsRelationManager::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 🔒 QUERY FILTER (ROLE BASED)
    |--------------------------------------------------------------------------
    */
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        return parent::getEloquentQuery()
            ->when(
                !$user->hasRole(['super_admin', 'kepala_inspektorat', 'staff']),
                function ($query) use ($user) {
                    $query->whereHas('auditAssignment', function ($q) use ($user) {
                        $q->where('ketua_tim_id', $user->id)
                          ->orWhereHas('members', fn ($q2) =>
                              $q2->where('user_id', $user->id)
                          );
                    });
                }
            );
    }

    /*
    |--------------------------------------------------------------------------
    | 🔐 AUTHORIZATION
    |--------------------------------------------------------------------------
    */

    public static function canViewAny(): bool
    {
        return Auth::check(); // semua user login boleh lihat menu
    }

    public static function canView($record): bool
    {
        $user = Auth::user();

        // ✅ full akses
        if ($user->hasRole(['super_admin', 'kepala_inspektorat', 'staff'])) {
            return true;
        }

        // ✅ ketua / anggota tim
        return optional($record->auditAssignment)->ketua_tim_id === $user->id ||
            optional($record->auditAssignment)
                ?->members()
                ->where('user_id', $user->id)
                ->exists();
    }

    public static function canCreate(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'ketua_tim']);
    }

    public static function canEdit($record): bool
    {
        return static::canView($record);
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    /*
    |--------------------------------------------------------------------------
    | 📄 PAGES
    |--------------------------------------------------------------------------
    */
    public static function getPages(): array
    {
        return [
            'index'  => ListLhps::route('/'),
            'create' => CreateLhp::route('/create'),
            'view'   => ViewLhp::route('/{record}'),
            'edit'   => EditLhp::route('/{record}/edit'),
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