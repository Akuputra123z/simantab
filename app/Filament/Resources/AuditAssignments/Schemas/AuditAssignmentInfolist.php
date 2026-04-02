<?php

namespace App\Filament\Resources\AuditAssignments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditAssignmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ================= DETAIL PENUGASAN =================
                Section::make('Detail Penugasan')
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('auditProgram.nama_program')
                            ->label('Program Audit')
                            ->color('primary')
                            ->placeholder('-'),

                        // 🔥 TAMBAHAN NOMOR SURAT
                        TextEntry::make('nomor_surat')
                            ->label('Nomor Surat Tugas')
                            ->badge()
                            ->color('info')
                            ->copyable()
                            ->copyMessage('Nomor surat disalin')
                            ->placeholder('-'),

                        Grid::make(2)->schema([
                            TextEntry::make('unitDiperiksa.nama_unit')
                                ->label('Unit Terperiksa')
                                ->placeholder('-'),

                            TextEntry::make('unitDiperiksa.kategori')
                                ->label('Kategori Unit')
                                ->badge()
                                ->placeholder('-'),
                        ]),

                        TextEntry::make('nama_tim')
                            ->label('Nama Tim Audit')
                            ->icon('heroicon-m-users')
                            ->placeholder('-'),
                    ]),

                // ================= STATUS & WAKTU =================
                Section::make('Status & Waktu')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'draft' => 'Draft',
                                'berjalan' => 'Berjalan',
                                'selesai' => 'Selesai',
                                default => ucfirst($state ?? '-'),
                            })
                            ->color(fn ($state) => match ($state) {
                                'draft' => 'gray',
                                'berjalan' => 'warning',
                                'selesai' => 'success',
                                default => 'primary',
                            }),

                        TextEntry::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->date('d F Y')
                            ->placeholder('-'),

                        TextEntry::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->date('d F Y')
                            ->color(fn ($record) =>
                                $record->tanggal_selesai?->isPast() ? 'danger' : 'gray'
                            )
                            ->placeholder('-'),
                    ]),

                // ================= PERSONEL =================
                Section::make('Personel Tim')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([

                            TextEntry::make('ketuaTim.name')
                                ->label('Ketua Tim')
                                ->icon('heroicon-m-user-circle')
                                ->placeholder('-'),

                            TextEntry::make('members.name')
                                ->label('Anggota Tim')
                                ->bulleted()
                                ->listWithLineBreaks()
                                ->placeholder('Belum ada anggota'),
                        ]),
                    ]),
            ]);
    }
}