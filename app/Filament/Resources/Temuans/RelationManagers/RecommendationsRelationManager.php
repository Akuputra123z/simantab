<?php

namespace App\Filament\Resources\Temuans\RelationManagers;

use App\Models\KodeRekomendasi;
use App\Models\Recommendation;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecommendationsRelationManager extends RelationManager
{
    protected static string $relationship = 'recommendations';

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Input Rekomendasi')
                ->description('Pilih kode yang sesuai dengan temuan untuk memudahkan pelaporan.')
                ->columns(2)
                ->schema([
                    Select::make('kode_rekomendasi_id')
                        ->label('Kode Rekomendasi')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->options(fn () => $this->getSmartOptions())
                        ->helperText('⭐ Menandakan kode yang paling sesuai dengan kriteria Temuan ini.'),

                    Textarea::make('uraian_rekom')
                        ->label('Uraian Instruksi')
                        ->placeholder('Contoh: Menginstruksikan kepada Kepala Dinas untuk menyetorkan kelebihan bayar ke Kas Daerah...')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),

                    TextInput::make('nilai_rekom')
                        ->label('Nominal Rekomendasi')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->helperText('Isi 0 jika rekomendasi bersifat non-finansial (Administrasi).'),

                    DatePicker::make('batas_waktu')
                        ->label('Tenggat Waktu')
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->default(now()->addDays(60)) // Standar 60 hari tindak lanjut
                        ->hint('Default: 60 hari ke depan'),
                ]),

        ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([

            TextEntry::make('kodeRekomendasi.kode')
                ->label('Kode Rekomendasi')
                ->placeholder('-'),

            TextEntry::make('uraian_rekom')
                ->columnSpanFull(),

            TextEntry::make('nilai_rekom')
                ->money('IDR', locale: 'id'),

            TextEntry::make('nilai_tl_selesai')
                ->money('IDR', locale: 'id'),

            TextEntry::make('nilai_sisa')
                ->money('IDR', locale: 'id'),

            TextEntry::make('batas_waktu')
                ->date()
                ->placeholder('-'),

            TextEntry::make('status')
                ->badge(),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('uraian_rekom')

            ->columns([

                TextColumn::make('kodeRekomendasi.kode')
                    ->label('ID')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('uraian_rekom')
                    ->label('Uraian')
                    ->wrap()
                    ->lineClamp(3) // Batasi baris agar tabel tetap rapi
                    ->searchable(),

                TextColumn::make('nilai_rekom')
                    ->label('Nilai Rekom')
                    ->money('IDR')
                    ->alignment('right')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Progres')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        Recommendation::STATUS_BELUM => 'danger',
                        Recommendation::STATUS_PROSES => 'warning',
                        Recommendation::STATUS_SELESAI => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record) => $record->label_status),

                TextColumn::make('batas_waktu')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->isJatuhTempo() ? 'danger' : 'gray')
                    ->description(fn ($record) => $record->isJatuhTempo() ? 'Melewati batas!' : null),
            ])

            ->filters([
                TrashedFilter::make(),
            ])

            ->headerActions([
                CreateAction::make(),
                // AssociateAction::make(),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])

            ->modifyQueryUsing(fn (Builder $query) =>
                $query->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ])
            );
    }


    
    protected function getSmartOptions(): array
    {
        $temuan = $this->getOwnerRecord();
        $alternatif = $temuan->kodeTemuan?->alternatif_rekom ?? [];

        $semua = KodeRekomendasi::query()->active()->orderBy('kode')->get();

        $disarankan = $semua->filter(fn ($k) => in_array($k->kode_numerik, $alternatif))
            ->mapWithKeys(fn ($k) => [$k->id => "⭐ {$k->kode} — {$k->deskripsi}"]);

        $lainnya = $semua->filter(fn ($k) => !in_array($k->kode_numerik, $alternatif))
            ->mapWithKeys(fn ($k) => [$k->id => "📁 {$k->kode} — {$k->deskripsi}"]);

        return [
            'Sesuai Kriteria Temuan' => $disarankan->toArray(),
            'Kategori Lainnya' => $lainnya->toArray(),
        ];
    }
}