<?php

namespace App\Filament\Resources\AuditAssignments;

use App\Filament\Resources\AuditAssignments\Pages\CreateAuditAssignment;
use App\Filament\Resources\AuditAssignments\Pages\EditAuditAssignment;
use App\Filament\Resources\AuditAssignments\Pages\ListAuditAssignments;
use App\Filament\Resources\AuditAssignments\Pages\ViewAuditAssignment;
use App\Filament\Resources\AuditAssignments\Schemas\AuditAssignmentForm;
use App\Filament\Resources\AuditAssignments\Schemas\AuditAssignmentInfolist;
use App\Filament\Resources\AuditAssignments\Tables\AuditAssignmentsTable;
use App\Models\AuditAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AuditAssignmentResource extends Resource
{
    protected static ?string $model = AuditAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'nama_tim';
    protected static bool $shouldRegisterNavigation = true;

    public static function getNavigationLabel(): string
    {
        return 'Penugasan Audit';
    }
    

     protected static string | UnitEnum | null $navigationGroup = 'Perencanaan Audit';

    public static function form(Schema $schema): Schema
    {
        return AuditAssignmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AuditAssignmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditAssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LhpsRelationManager::class,
        ];
    }

   public static function getEloquentQuery(): Builder
{
    $user = Auth::user();

    return parent::getEloquentQuery()
        ->when(!$user->hasRole('super_admin'), function (Builder $query) use ($user) {
            $query->where(function (Builder $q) use ($user) {
                $q->where('ketua_tim_id', $user->id)
                  ->orWhereHas('members', function ($q) use ($user) {
                      $q->where('user_id', $user->id); // ⬅️ penting (pivot)
                  });
            });
        });
}

    public static function getPages(): array
    {
        return [
            'index'  => ListAuditAssignments::route('/'),
            'create' => CreateAuditAssignment::route('/create'),
            'view'   => ViewAuditAssignment::route('/{record}'),
            'edit'   => EditAuditAssignment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}