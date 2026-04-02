<?php

namespace App\Filament\Resources\TindakLanjuts\RelationManagers;

use App\Models\Attachment;
use App\Models\TindakLanjutCicilan;
use Filament\Actions\{BulkActionGroup, CreateAction, DeleteAction, DeleteBulkAction, EditAction, ForceDeleteAction, ForceDeleteBulkAction, RestoreAction, RestoreBulkAction, ViewAction};
use Filament\Forms\Components\{DatePicker, FileUpload, Hidden, Placeholder, Select, Textarea, TextInput};
use Filament\Infolists\Components\{RepeatableEntry, TextEntry};
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\{TextColumn};
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\{SelectFilter, TrashedFilter};
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CicilansRelationManager extends RelationManager
{
    protected static string $relationship = 'cicilans';
    protected static ?string $title = 'Realisasi Cicilan';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return (bool) $ownerRecord->is_cicilan;
    }

    // =========================
    // FORM
    // =========================
    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Progress Cicilan')
                ->schema([
                    Placeholder::make('progress')
                        ->content(fn ($livewire) => self::progressText($livewire)),
                ]),

            Section::make('Pembayaran')
                ->columns(2)
                ->schema([

                    TextInput::make('ke')
                        ->numeric()
                        ->required()
                        ->default(fn ($livewire) => $livewire->ownerRecord->nextKeCicilan())
                        ->unique(
                            'tindak_lanjut_cicilans',
                            'ke',
                            ignoreRecord: true,
                            modifyRuleUsing: fn ($rule, $livewire) =>
                                $rule->where('tindak_lanjut_id', $livewire->ownerRecord->id)
                        ),

                    DatePicker::make('tanggal_bayar')
                        ->required()
                        ->default(now())
                        ->native(false),

                    TextInput::make('nilai_bayar')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->minValue(1)
                        ->columnSpanFull()
                        ->default(fn ($livewire) =>
                            (float) $livewire->ownerRecord->nilai_per_cicilan_rencana
                        )
                        ->rules([
                            fn ($livewire, $record) => function ($attr, $value, $fail) use ($livewire, $record) {
                                $tl = $livewire->ownerRecord;

                                $sisa = (float) $tl->sisa_belum_bayar;
                                $max  = $record
                                    ? $sisa + (float) $record->nilai_bayar
                                    : $sisa;

                                if ($max <= 0) {
                                    return $fail('Sudah lunas.');
                                }

                                if ($value > $max) {
                                    $fail('Melebihi sisa pembayaran.');
                                }
                            }
                        ]),

                    TextInput::make('nomor_bukti'),

                    Select::make('jenis_bayar')
                        ->options(self::jenisBayarOptions())
                        ->default('setor_kas')
                        ->required(),
                ]),

            Section::make('Lampiran')
                ->schema([
                    \Filament\Forms\Components\Repeater::make('attachments')
                        ->relationship()
                        ->schema([
                            FileUpload::make('file_path')
                                ->disk('public')
                                ->directory('audit/cicilan')
                                ->required(),

                            TextInput::make('file_name'),

                            Hidden::make('uploaded_by')
                                ->default(fn () => auth()->id()),
                        ])
                ]),

            Section::make('Verifikasi')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(self::statusOptions())
                        ->default(TindakLanjutCicilan::STATUS_MENUNGGU)
                        ->required(),

                    Select::make('diverifikasi_oleh')
                        ->relationship('diverifikator', 'name')
                        ->searchable()
                        ->preload(),

                    DatePicker::make('diverifikasi_pada'),

                    Textarea::make('catatan_verifikasi')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->with('attachments')
                    ->withoutGlobalScopes([SoftDeletingScope::class])
            )
            ->defaultSort('ke')
            ->description(fn ($livewire) => self::progressSummary($livewire))
            ->columns([

                TextColumn::make('ke')->badge(),

                TextColumn::make('tanggal_bayar')->date(),

                TextColumn::make('nilai_bayar')
                    ->money('IDR')
                    ->summarize(Sum::make()->money('IDR')),

                TextColumn::make('jenis_bayar')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::jenisBayarOptions()[$state] ?? $state),

               

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::statusOptions()[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        TindakLanjutCicilan::STATUS_DITERIMA => 'success',
                        TindakLanjutCicilan::STATUS_DITOLAK  => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::statusOptions()),
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    // =========================
    // 🔥 HELPERS
    // =========================

    private static function progressText($livewire): string
    {
        $tl = $livewire->ownerRecord;

        $total = (float) $tl->recommendation?->nilai_rekom;
        $paid  = (float) $tl->total_terbayar;
        $sisa  = (float) $tl->sisa_belum_bayar;

        return "Total: Rp " . number_format($total, 0, ',', '.') .
            " | Terbayar: Rp " . number_format($paid, 0, ',', '.') .
            " | Sisa: Rp " . number_format($sisa, 0, ',', '.');
    }

    private static function progressSummary($livewire): string
    {
        $tl = $livewire->ownerRecord;

        $sisa = (float) $tl->sisa_belum_bayar;
        $paid = number_format((float) $tl->total_terbayar, 0, ',', '.');

        return $sisa <= 0
            ? "✅ Lunas — Rp {$paid}"
            : "Terbayar: Rp {$paid}";
    }

    private static function jenisBayarOptions(): array
    {
        return [
            'setor_kas' => 'Setor Kas',
            'transfer' => 'Transfer',
            'tunai' => 'Tunai',
            'pengembalian_barang' => 'Pengembalian Barang',
            'perbaikan_fisik' => 'Perbaikan Fisik',
            'lainnya' => 'Lainnya',
        ];
    }

    private static function statusOptions(): array
    {
        return [
            TindakLanjutCicilan::STATUS_MENUNGGU => 'Menunggu',
            TindakLanjutCicilan::STATUS_DITERIMA => 'Diterima',
            TindakLanjutCicilan::STATUS_DITOLAK  => 'Ditolak',
        ];
    }
}