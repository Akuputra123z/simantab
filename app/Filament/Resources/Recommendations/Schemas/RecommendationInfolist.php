<?php

namespace App\Filament\Resources\Recommendations\Schemas;

use App\Models\Attachment;
use App\Models\Recommendation;
use App\Models\Temuan;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RecommendationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── RINGKASAN ─────────────────────────────────────────────────
                Section::make('Ringkasan')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('temuan.lhp.nomor_lhp')
                            ->label('Nomor LHP')
                            ->weight('bold')
                            ->default('-'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                Recommendation::STATUS_BELUM   => 'Belum Ditindaklanjuti',
                                Recommendation::STATUS_PROSES  => 'Dalam Proses',
                                Recommendation::STATUS_SELESAI => 'Selesai',
                                default                        => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                Recommendation::STATUS_BELUM   => 'danger',
                                Recommendation::STATUS_PROSES  => 'warning',
                                Recommendation::STATUS_SELESAI => 'success',
                                default                        => 'gray',
                            }),

                        // progress() sudah ada di model Recommendation
                        TextEntry::make('progress_persen')
                            ->label('Progress')
                            ->suffix('%')
                            ->badge()
                            ->getStateUsing(fn ($record) => $record->progress())
                            ->color(fn ($state) => match (true) {
                                $state >= 100 => 'success',
                                $state > 0    => 'warning',
                                default       => 'danger',
                            }),
                    ]),

                // ── INFORMASI LHP ─────────────────────────────────────────────
                Section::make('Informasi LHP')
                    ->columns(3)
                    ->collapsible()
                    ->schema([

                        TextEntry::make('temuan.lhp.nomor_lhp')
                            ->label('Nomor LHP')
                            ->default('-'),

                        TextEntry::make('temuan.lhp.tanggal_lhp')
                            ->label('Tanggal LHP')
                            ->date('d M Y')
                            ->default('-'),

                        TextEntry::make('temuan.lhp.status')
                            ->label('Status LHP')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'draft'          => 'Draft',
                                'final'          => 'Final',
                                'ditandatangani' => 'Ditandatangani',
                                default          => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'draft'          => 'gray',
                                'final'          => 'warning',
                                'ditandatangani' => 'success',
                                default          => 'gray',
                            }),
                    ]),

                // ── INFORMASI TEMUAN ──────────────────────────────────────────
                Section::make('Informasi Temuan')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('temuan.kodeTemuan.kode')
                            ->label('Kode Temuan')
                            ->badge()
                            ->color('primary')
                            ->default('-'),

                        TextEntry::make('temuan.nilai_temuan')
                            ->label('Nilai Temuan')
                            ->money('IDR')
                            ->default(0),

                        TextEntry::make('temuan.status_tl')
                            ->label('Status TL Temuan')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                Temuan::STATUS_BELUM   => 'Belum',
                                Temuan::STATUS_PROSES  => 'Dalam Proses',
                                Temuan::STATUS_SELESAI => 'Selesai',
                                default                => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                Temuan::STATUS_BELUM   => 'danger',
                                Temuan::STATUS_PROSES  => 'warning',
                                Temuan::STATUS_SELESAI => 'success',
                                default                => 'gray',
                            }),

                        TextEntry::make('temuan.kondisi')
                            ->label('Kondisi')
                            ->columnSpanFull()
                            ->default('-'),

                        TextEntry::make('temuan.sebab')
                            ->label('Sebab')
                            ->columnSpanFull()
                            ->default('-'),

                        TextEntry::make('temuan.akibat')
                            ->label('Akibat')
                            ->columnSpanFull()
                            ->default('-'),
                    ]),

                // ── DATA REKOMENDASI ──────────────────────────────────────────
                Section::make('Data Rekomendasi')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('kodeRekomendasi.kode')
                            ->label('Kode Rekomendasi')
                            ->badge()
                            ->color('info')
                            ->default('-'),

                        TextEntry::make('jenis_rekomendasi')
                            ->label('Jenis')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'uang'         => 'Uang',
                                'barang'       => 'Barang',
                                'administrasi' => 'Administrasi',
                                default        => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'uang'         => 'success',
                                'barang'       => 'info',
                                'administrasi' => 'warning',
                                default        => 'gray',
                            }),

                        TextEntry::make('batas_waktu')
                            ->label('Batas Waktu TL')
                            ->date('d M Y')
                            ->default('-')
                            ->color(fn ($record) => $record->isJatuhTempo() ? 'danger' : null),

                        TextEntry::make('uraian_rekom')
                            ->label('Uraian Rekomendasi')
                            ->columnSpanFull()
                            ->default('-'),

                        TextEntry::make('nilai_rekom')
                            ->label('Nilai Rekomendasi')
                            ->money('IDR')
                            ->default(0),

                        TextEntry::make('nilai_tl_selesai')
                            ->label('Nilai TL Selesai')
                            ->money('IDR')
                            ->color('success')
                            ->default(0),

                        TextEntry::make('nilai_sisa')
                            ->label('Nilai Sisa')
                            ->money('IDR')
                            ->color('danger')
                            ->default(0),
                    ]),

                // ── LAMPIRAN ──────────────────────────────────────────────────
                Section::make('Lampiran')
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->attachments->isEmpty())
                    ->schema([

                        RepeatableEntry::make('attachments')
                            ->label('')
                            ->schema([

                                TextEntry::make('jenis_bukti')
                                    ->label('Jenis')
                                    ->formatStateUsing(fn ($state) =>
                                        Attachment::JENIS_BUKTI['tindak_lanjut'][$state]
                                            ?? ucfirst(str_replace('_', ' ', $state ?? '-'))
                                    )
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('file_name')
                                    ->label('Keterangan')
                                    ->default('-'),

                                TextEntry::make('file_size_formatted')
                                    ->label('Ukuran')
                                    ->default('-'),

                                TextEntry::make('file_url')
                                    ->label('File')
                                    ->formatStateUsing(fn () => 'Lihat / Unduh')
                                    ->url(fn ($record) => $record->file_url)
                                    ->openUrlInNewTab()
                                    ->color('primary'),
                            ])
                            ->columns(4),
                    ]),

                // ── HISTORI TINDAK LANJUT ─────────────────────────────────────
                Section::make('Histori Tindak Lanjut')
                    ->collapsible()
                    ->schema([

                        RepeatableEntry::make('tindakLanjuts')
                            ->label('')
                            ->schema([

                                TextEntry::make('jenis_penyelesaian')
                                    ->label('Jenis')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'setor_kas'              => 'Setor Kas',
                                        'pengembalian_barang'    => 'Pengembalian Barang',
                                        'perbaikan_administrasi' => 'Perbaikan Administrasi',
                                        'cicilan'                => 'Cicilan',
                                        default                  => $state ?? '-',
                                    })
                                    ->color('primary'),

                                TextEntry::make('nilai_tindak_lanjut')
                                    ->label('Nilai')
                                    ->money('IDR')
                                    ->default(0),

                                TextEntry::make('total_terbayar')
                                    ->label('Terbayar')
                                    ->money('IDR')
                                    ->color('success')
                                    ->default(0),

                                TextEntry::make('sisa_belum_bayar')
                                    ->label('Sisa')
                                    ->money('IDR')
                                    ->color('danger')
                                    ->default(0),

                                TextEntry::make('status_verifikasi')
                                    ->label('Verifikasi')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'menunggu_verifikasi' => 'Menunggu',
                                        'berjalan'            => 'Berjalan',
                                        'lunas'               => 'Lunas',
                                        'ditolak'             => 'Ditolak',
                                        default               => $state,
                                    })
                                    ->color(fn ($state) => match ($state) {
                                        'lunas'               => 'success',
                                        'berjalan'            => 'info',
                                        'menunggu_verifikasi' => 'warning',
                                        'ditolak'             => 'danger',
                                        default               => 'gray',
                                    }),

                                TextEntry::make('diverifikasi_pada')
                                    ->label('Tgl Verifikasi')
                                    ->dateTime('d M Y')
                                    ->default('-'),

                                TextEntry::make('catatan_tl')
                                    ->label('Catatan')
                                    ->columnSpanFull()
                                    ->default('-'),
                            ])
                            ->columns(6)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}