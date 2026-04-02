<?php

namespace App\Filament\Resources\AuditPrograms;

use App\Filament\Resources\AuditPrograms\Pages\CreateAuditProgram;
use App\Filament\Resources\AuditPrograms\Pages\EditAuditProgram;
use App\Filament\Resources\AuditPrograms\Pages\ListAuditPrograms;
use App\Filament\Resources\AuditPrograms\Pages\ViewAuditProgram;
use App\Filament\Resources\AuditPrograms\Schemas\AuditProgramForm;
use App\Filament\Resources\AuditPrograms\Schemas\AuditProgramInfolist;
use App\Filament\Resources\AuditPrograms\Tables\AuditProgramsTable;
use App\Models\AuditProgram;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuditProgramResource extends Resource
{
    protected static ?string $model = AuditProgram::class;

    protected static string | UnitEnum | null $navigationGroup = 'Perencanaan Audit';
    public static function getNavigationLabel(): string
{
    return 'Program Kerja';

    
}

protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'AuditProgram';

    public static function form(Schema $schema): Schema
    {
        return AuditProgramForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AuditProgramInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditProgramsTable::configure($table);
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
            'index' => ListAuditPrograms::route('/'),
            'create' => CreateAuditProgram::route('/create'),
            'view' => ViewAuditProgram::route('/{record}'),
            'edit' => EditAuditProgram::route('/{record}/edit'),
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
