<?php

namespace App\Filament\Resources\LhpReports;

use App\Filament\Resources\LhpReports\Pages\ManageLhpReports;
use App\Models\Lhp;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use UnitEnum;

class LhpReportResource extends Resource
{
    protected static ?string $model = Lhp::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;
    protected static ?string $recordTitleAttribute = 'nomor_lhp';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return 'Laporan LHP';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                    $user = auth()->user();

                    return Lhp::query()
                        ->select([
                            'id',
                            'nomor_lhp',
                'irban',
                'status',
                'audit_assignment_id'
            ])
            ->with([
                'statistik:id,lhp_id,total_temuan,total_rekomendasi,total_kerugian,persen_selesai_gabungan',
                'auditAssignment:id,ketua_tim_id',
            ])

            // ✅ STATUS WAJIB
            ->whereIn('status', ['final', 'ditandatangani'])

            // ✅ PROGRESS 100%
            ->whereHas('statistik', function ($q) {
                $q->where('persen_selesai_gabungan', '>=', 100);
            })

            // ✅ ROLE FILTER
            ->when(
                ! $user->hasAnyRole(['super_admin', 'kepala_inspektorat', 'staff']),
                function (Builder $q) use ($user) {
                    $q->whereHas('auditAssignment', function (Builder $q2) use ($user) {
                        $q2->where('ketua_tim_id', $user->id)
                        ->orWhereHas('members', fn ($q3) =>
                            $q3->where('user_id', $user->id)
                        );
                    });
                }
            );
    })

            ->columns([
                TextColumn::make('nomor_lhp')
                    ->label('Nomor LHP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('irban')
                    ->label('Wilayah/Irban'),

                TextColumn::make('statistik.total_temuan')
                    ->label('Temuan')
                    ->alignCenter(),

                TextColumn::make('statistik.total_rekomendasi')
                    ->label('Rekom')
                    ->alignCenter(),

                TextColumn::make('statistik.total_kerugian')
                    ->label('Nilai Kerugian')
                    ->money('IDR', locale: 'id')
                    ->summarize(Sum::make()->label('Total')),

                TextColumn::make('statistik.persen_selesai_gabungan')
                    ->label('Progress')
                    ->suffix('%')
                    ->numeric(2)
                    ->color('success'),
            ])

            ->recordActions([
                Action::make('print_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->url(fn ($record) => route('pdf.laporan-lhp', $record))
                    ->openUrlInNewTab(),

                ForceDeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),

                RestoreAction::make()
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('super_admin')),

                    RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('super_admin')),
                ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 🔐 AUTHORIZATION
    |--------------------------------------------------------------------------
    */

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canView($record): bool
    {
        $user = auth()->user();

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

    public static function getPages(): array
    {
        return [
            'index' => ManageLhpReports::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class
            ]);
    }
}