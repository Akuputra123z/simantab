<?php

namespace App\Filament\Resources\Recommendations\Tables;

use Filament\Actions\DeleteAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;

class RecommendationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('temuan_lhp_nomor')
                    ->label('Nomor LHP')
                    ->getStateUsing(fn ($record) => $record->temuan?->lhp?->nomor_lhp)
                    ->searchable(false),

                TextColumn::make('kode_rekomendasi_kode')
                    ->label('Kode')
                    ->getStateUsing(fn ($record) => $record->kodeRekomendasi?->kode)
                    ->badge('info'),

                TextColumn::make('uraian_rekom')
                    ->label('Uraian Rekomendasi')
                    ->limit(70)
                    ->tooltip(fn ($record) => $record->uraian_rekom)
                    ->wrap(),

                TextColumn::make('nilai_rekom')
                    ->label('Nilai Rekomendasi')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('batas_waktu')
                    ->label('Batas Waktu')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->batas_waktu && $record->batas_waktu->isPast() ? 'danger' : 'primary'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'belum_ditindaklanjuti'       => 'danger',
                        'dalam_proses'                => 'warning',
                        'selesai'                     => 'success',
                        'tidak_dapat_ditindaklanjuti' => 'gray',
                        default                       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'belum_ditindaklanjuti'       => 'Belum Ditindaklanjuti',
                        'dalam_proses'                => 'Dalam Proses',
                        'selesai'                     => 'Selesai',
                        'tidak_dapat_ditindaklanjuti' => 'Tidak Dapat Ditindaklanjuti',
                        default                       => $state,
                    }),

                TextColumn::make('tindak_lanjuts_count')
                    ->label('Tindak Lanjut')
                    ->counts('tindakLanjuts')
                    ->badge('primary'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                DeleteAction::make()
                // ViewAction::make(),
                // EditAction::make(),
            ])
            ->bulkActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ]);
    }
}