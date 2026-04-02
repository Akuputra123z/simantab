<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
Section::make('Informasi Aktivitas')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Waktu')
                        ->dateTime('d M Y, H:i:s')
                        ->icon('heroicon-m-clock'),
 
                    TextEntry::make('causer.name')
                        ->label('Pengguna')
                        ->default('— Sistem —')
                        ->icon('heroicon-m-user-circle'),
                        // ->description(fn ($record) => $record->causer?->email ?? ''),
 
                    TextEntry::make('log_name')
                        ->label('Modul')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'lhp'            => '📋 LHP',
                            'temuan'         => '🔍 Temuan',
                            'recommendation' => '📌 Rekomendasi',
                            'tindaklanjut'   => '✅ Tindak Lanjut',
                            'auth'           => '🔐 Auth',
                            default          => ucfirst($state ?? '-'),
                        })
                        ->color(fn ($state) => match ($state) {
                            'lhp'            => 'primary',
                            'temuan'         => 'warning',
                            'recommendation' => 'info',
                            'tindaklanjut'   => 'success',
                            'auth'           => 'gray',
                            default          => 'gray',
                        }),
 
                    TextEntry::make('event')
                        ->label('Aksi')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'created'  => '✅ Dibuat',
                            'updated'  => '✏️ Diubah',
                            'deleted'  => '🗑️ Dihapus',
                            'restored' => '♻️ Dipulihkan',
                            default    => ucfirst($state ?? '-'),
                        })
                        ->color(fn ($state) => match ($state) {
                            'created'  => 'success',
                            'updated'  => 'info',
                            'deleted'  => 'danger',
                            'restored' => 'warning',
                            default    => 'gray',
                        }),
 
                    TextEntry::make('description')
                        ->label('Keterangan')
                        ->columnSpanFull(),
 
                    TextEntry::make('subject_type')
                        ->label('Data Terkait')
                        ->columnSpanFull()
                        ->formatStateUsing(fn ($state, $record) =>
                            class_basename($state ?? '') . ' #' . ($record->subject_id ?? '-')
                        )
                       
                ]),
 
            // ── INFO PERANGKAT ────────────────────────────────────────────
            Section::make('Info Perangkat')
                ->icon('heroicon-o-computer-desktop')
                ->columns(2)
                ->visible(fn ($record) =>
                    !empty($record->properties['ip']) ||
                    !empty($record->properties['user_agent'])
                )
                ->schema([
                    TextEntry::make('properties.ip')
                        ->label('IP Address')
                        ->default('-')
                        ->icon('heroicon-m-wifi'),
 
                    TextEntry::make('properties.user_agent')
                        ->label('Browser / Device')
                        ->default('-')
                        ->limit(80)
                        ->tooltip(fn ($record) => $record->properties['user_agent'] ?? '-'),
                ]),
 
            // ── DETAIL PERUBAHAN ──────────────────────────────────────────
            Section::make('Detail Perubahan')
                ->icon('heroicon-o-arrows-right-left')
                ->visible(fn ($record) =>
                    !empty($record->properties['attributes']) ||
                    !empty($record->properties['old'])
                )
                ->schema([
                    TextEntry::make('diff')
                        ->label('')
                        ->columnSpanFull()
                        ->html()
                        ->getStateUsing(function ($record) {
                            $props   = is_array($record->properties)
                                ? $record->properties
                                : $record->properties->toArray();
 
                            $old     = $props['old'] ?? [];
                            $new     = $props['attributes'] ?? [];
                            $changes = [];
 
                            // Updated: hanya field yang berubah
                            if (!empty($old)) {
                                foreach ($new as $key => $val) {
                                    if (($old[$key] ?? null) !== $val) {
                                        $changes[$key] = [
                                            'old' => $old[$key] ?? null,
                                            'new' => $val,
                                        ];
                                    }
                                }
                            }
 
                            // Created: semua atribut baru
                            if (empty($old) && !empty($new) && $record->event === 'created') {
                                foreach ($new as $key => $val) {
                                    $changes[$key] = ['old' => null, 'new' => $val];
                                }
                            }
 
                            if (empty($changes)) {
                                return '<p style="color:#9ca3af;font-style:italic;font-size:0.8rem;">Tidak ada detail perubahan yang tersimpan.</p>';
                            }
 
                            $rows = collect($changes)
                                ->map(fn ($diff, $field) =>
                                    '<tr style="border-bottom:1px solid #f3f4f6;">'
                                    . '<td style="padding:7px 10px;font-family:monospace;font-weight:600;color:#374151;white-space:nowrap;">'
                                        . e($field)
                                    . '</td>'
                                    . '<td style="padding:7px 10px;color:#dc2626;word-break:break-all;">'
                                        . (is_null($diff['old'])
                                            ? '<em style="color:#9ca3af">(kosong)</em>'
                                            : '<span style="text-decoration:line-through">' . e(is_array($diff['old']) ? json_encode($diff['old']) : $diff['old']) . '</span>')
                                    . '</td>'
                                    . '<td style="padding:7px 10px;color:#16a34a;font-weight:600;word-break:break-all;">'
                                        . (is_null($diff['new'])
                                            ? '<em style="color:#9ca3af">(kosong)</em>'
                                            : e(is_array($diff['new']) ? json_encode($diff['new']) : $diff['new']))
                                    . '</td>'
                                    . '</tr>'
                                )
                                ->join('');
 
                            return '
                                <div style="font-size:0.8rem;">
                                    <p style="margin:0 0 8px;font-size:0.7rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">
                                        ' . count($changes) . ' field berubah
                                    </p>
                                    <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                                        <thead>
                                            <tr style="background:#f3f4f6;">
                                                <th style="padding:8px 10px;text-align:left;font-weight:600;color:#374151;width:25%;">Field</th>
                                                <th style="padding:8px 10px;text-align:left;font-weight:600;color:#dc2626;width:37.5%;">✗ Sebelum</th>
                                                <th style="padding:8px 10px;text-align:left;font-weight:600;color:#16a34a;width:37.5%;">✓ Sesudah</th>
                                            </tr>
                                        </thead>
                                        <tbody>' . $rows . '</tbody>
                                    </table>
                                </div>';
                        }),
                ]),
            ]);
    }

     public static function eventLabel(?string $state): string
    {
        return match ($state) {
            'created'  => '✅ Dibuat',
            'updated'  => '✏️ Diubah',
            'deleted'  => '🗑️ Dihapus',
            'restored' => '♻️ Dipulihkan',
            default    => ucfirst($state ?? '-'),
        };
    }

    public static function eventColor(?string $state): string
    {
        return match ($state) {
            'created'  => 'success',
            'updated'  => 'info',
            'deleted'  => 'danger',
            'restored' => 'warning',
            default    => 'gray',
        };
    }

    public static function logNameLabel(?string $state): string
    {
        return match ($state) {
            'lhp'            => '📋 LHP',
            'temuan'         => '🔍 Temuan',
            'recommendation' => '📌 Rekomendasi',
            'tindaklanjut'   => '✅ Tindak Lanjut',
            'auth'           => '🔐 Login/Logout',
            default          => ucfirst($state ?? '-'),
        };
    }

    public static function logNameColor(?string $state): string
    {
        return match ($state) {
            'lhp'            => 'primary',
            'temuan'         => 'warning',
            'recommendation' => 'info',
            'tindaklanjut'   => 'success',
            'auth'           => 'gray',
            default          => 'gray',
        };
    }
}