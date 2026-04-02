<?php

namespace App\Filament\Resources\Temuans\Schemas;

use App\Models\Temuan;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;

class TemuanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── IDENTITAS ─────────────────────────────────────────────
                Section::make('Identitas Temuan')
                    ->icon('heroicon-o-document-text')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('lhp.nomor_lhp')
                            ->label('Nomor LHP')
                            ->icon('heroicon-o-document'),

                        TextEntry::make('lhp.auditAssignment.unitDiperiksa.nama_unit')
                            ->label('Unit Diperiksa')
                            ->icon('heroicon-o-building-office'),

                        TextEntry::make('kodeTemuan.kode')
                            ->label('Kode Temuan')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('kondisi')
                            ->label('Kondisi')
                            ->columnSpanFull()
                            ->html(),

                        TextEntry::make('sebab')
                            ->label('Sebab / Root Cause')
                            ->columnSpan(2)
                            ->placeholder('—'),

                        TextEntry::make('akibat')
                            ->label('Akibat')
                            ->placeholder('—'),
                    ]),

                // ── NILAI KERUGIAN ────────────────────────────────────────
                Section::make('Nilai Kerugian')
                    ->icon('heroicon-o-banknotes')
                    ->description('Rincian nilai kerugian per kategori (dalam Rupiah)')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nilai_kerugian_negara')
                            ->label('Kerugian Negara')
                            ->money('IDR')
                            ->icon('heroicon-o-building-library'),

                        TextEntry::make('nilai_kerugian_daerah')
                            ->label('Kerugian Daerah')
                            ->money('IDR')
                            ->icon('heroicon-o-map'),

                        TextEntry::make('nilai_kerugian_desa')
                            ->label('Kerugian Desa')
                            ->money('IDR')
                            ->icon('heroicon-o-home'),

                        TextEntry::make('nilai_kerugian_bos_blud')
                            ->label('Kerugian BOS / BLUD')
                            ->money('IDR')
                            ->icon('heroicon-o-academic-cap'),

                        // Total — full width, menonjol
                        TextEntry::make('nilai_temuan')
                            ->label('Total Nilai Temuan')
                            ->money('IDR')
                            // ->size('lg')
                            ->weight('bold')
                            ->color('danger')
                            ->icon('heroicon-o-calculator')
                            ->columnSpanFull(),
                    ]),
                    Section::make('Pengembalian Barang / Aset')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->description('Informasi barang atau aset yang harus dikembalikan.')
                    ->columns(2)
                    ->visible(fn ($record): bool => filled($record->nama_barang))
                    ->schema([
                        TextEntry::make('nama_barang')
                            ->label('Nama Barang / Aset')
                            ->icon('heroicon-o-cube')
                            ->placeholder('—'),

                        TextEntry::make('jumlah_barang')
                            ->label('Jumlah / Volume')
                            ->icon('heroicon-o-hashtag')
                            ->placeholder('—'),

                        TextEntry::make('kondisi_barang')
                            ->label('Kondisi Barang')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        TextEntry::make('lokasi_barang')
                            ->label('Lokasi / Keberadaan Barang')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                // ── STATUS TINDAK LANJUT ──────────────────────────────────
                Section::make('Status Tindak Lanjut')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('status_tl')
                            ->label('Status')
                            ->badge()
                            ->size('lg')
                            ->color(fn (string $state): string => match ($state) {
                                Temuan::STATUS_BELUM   => 'danger',
                                Temuan::STATUS_PROSES  => 'warning',
                                Temuan::STATUS_SELESAI => 'success',
                                default                => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Temuan::STATUS_BELUM   => 'Belum Ditindaklanjuti',
                                Temuan::STATUS_PROSES  => 'Dalam Proses',
                                Temuan::STATUS_SELESAI => 'Selesai',
                                default                => $state,
                            }),
                    ]),

            ]);
    }
}