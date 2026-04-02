<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Filament\Resources\ActivityLogs\Schemas\ActivityLogInfolist;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ActivityLogsTable
{
    // ── Options — reuse dari Infolist agar tidak duplikat ─────────────────────

    private static function logNameOptions(): array
    {
        return [
            'lhp'            => '📋 LHP',
            'temuan'         => '🔍 Temuan',
            'recommendation' => '📌 Rekomendasi',
            'tindaklanjut'   => '✅ Tindak Lanjut',
            'auth'           => '🔐 Login/Logout',
        ];
    }

    private static function eventOptions(): array
    {
        return [
            'created'  => '✅ Dibuat',
            'updated'  => '✏️ Diubah',
            'deleted'  => '🗑️ Dihapus',
            'restored' => '♻️ Dipulihkan',
        ];
    }

    // ── Configure ─────────────────────────────────────────────────────────────

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->since()
                    ->sortable()
                    ->tooltip(fn ($record) => $record->created_at->format('d M Y, H:i:s')),

                TextColumn::make('causer.name')
                    ->label('Pengguna')
                    ->default('— Sistem —')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-user-circle')
                    ->description(fn ($record) => $record->causer?->email),

                TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ActivityLogInfolist::eventLabel($state ?? ''))
                    ->color(fn ($state) => ActivityLogInfolist::eventColor($state ?? '')),

                TextColumn::make('log_name')
                    ->label('Modul')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ActivityLogInfolist::logNameLabel($state ?? ''))
                    ->color(fn ($state) => ActivityLogInfolist::logNameColor($state ?? ''))
                    ->searchable(),
                TextColumn::make('subject_type')
                    ->label('Data')
                    ->formatStateUsing(fn ($state, $record) =>
                        class_basename($state ?? '') . ' #' . ($record->subject_id ?? '-')
                    )
                    ->description(fn ($record) =>
                        Str::words(
                            $record->subject?->nomor_lhp
                            ?? $record->subject?->kondisi
                            ?? $record->subject?->uraian_rekom
                            ?? $record->subject?->jenis_penyelesaian
                            ?? '-',
                            10, // jumlah kata
                            '...'
                        )
                    )
                    ->words(10), // untuk title utama
                TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(60)
                    ->tooltip(fn ($state) => $state)
                    ->searchable(),

                // Kolom ringkasan perubahan — hidden by default agar tabel ringan
                TextColumn::make('properties')
                    ->label('Ringkasan Perubahan')
                    ->formatStateUsing(fn ($record) => self::summarizeChanges($record))
                    ->wrap()
                    ->fontFamily('mono')
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('properties.ip')
                    ->label('IP Address')
                    ->default('-')
                    ->icon('heroicon-m-computer-desktop')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('log_name')
                    ->label('Modul')
                    ->options(self::logNameOptions()),

                SelectFilter::make('event')
                    ->label('Aksi')
                    ->options(self::eventOptions()),

                Filter::make('tanggal')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['dari'],   fn ($q) => $q->whereDate('created_at', '>=', $data['dari']))
                        ->when($data['sampai'], fn ($q) => $q->whereDate('created_at', '<=', $data['sampai']))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari'])   $indicators[] = 'Dari: ' . $data['dari'];
                        if ($data['sampai']) $indicators[] = 'Sampai: ' . $data['sampai'];
                        return $indicators;
                    }),
            ])

            ->recordActions([
                // ✅ ViewAction pakai infolist() yang sudah didefinisikan di Resource
                ViewAction::make()
                    ->label('Detail')
                    ->slideOver(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation(),
                ]),
            ])

            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->poll('60s')
            ->striped();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function summarizeChanges($record): string
    {
        $props = is_array($record->properties)
            ? $record->properties
            : $record->properties->toArray();

        // Auth log
        if ($record->log_name === 'auth') {
            return 'IP: ' . ($props['ip'] ?? '-');
        }

        $old = $props['old'] ?? [];
        $new = $props['attributes'] ?? [];

        if (empty($old) && empty($new)) return '-';

        $changes = [];
        foreach ($new as $key => $val) {
            if (($old[$key] ?? null) !== $val) {
                $changes[] = $key . ': ' . ($old[$key] ?? '∅') . ' → ' . ($val ?? '∅');
            }
        }

        if (empty($changes)) return '-';

        $extra  = count($changes) - 3;
        $shown  = array_slice($changes, 0, 3);

        return implode("\n", $shown)
            . ($extra > 0 ? "\n+" . $extra . ' lainnya...' : '');
    }

    
}