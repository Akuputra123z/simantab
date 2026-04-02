<?php

namespace App\Filament\Resources\TindakLanjuts\Schemas;

use App\Models\Attachment;
use App\Models\TindakLanjut;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TindakLanjutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── KONTEKS REKOMENDASI ───────────────────────────────────────
                // Tampilkan dari mana TL ini berasal tanpa harus buka halaman lain
                Section::make('Konteks Rekomendasi')
                   ->icon('heroicon-o-clipboard-document-check')
                    ->description('Sumber temuan dan instruksi rekomendasi dari LHP.')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('recommendation.temuan.lhp.nomor_lhp')
                            ->label('Nomor LHP')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        TextEntry::make('recommendation.temuan.lhp.irban')
                            ->label('Wilayah Irban')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('recommendation.jenis_rekomendasi')
                            ->label('Jenis Rekomendasi')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'uang'         => '💰 Uang',
                                'barang'       => '📦 Barang',
                                'administrasi' => '📄 Administrasi',
                                default        => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'uang'         => 'success',
                                'barang'       => 'info',
                                'administrasi' => 'warning',
                                default        => 'gray',
                            }),

                        TextEntry::make('recommendation.temuan.kondisi')
                            ->label('Kondisi Temuan')
                            ->columnSpanFull()
                            ->prose()
                            ->default('-'),

                        TextEntry::make('recommendation.uraian_rekom')
                            ->label('Uraian Rekomendasi')
                            ->columnSpanFull()
                            ->prose()
                            ->weight('medium')
                            ->color('gray')
                            ->default('-'),

                        TextEntry::make('recommendation.nilai_rekom')
                            ->label('Nilai Rekomendasi')
                            ->money('IDR')
                            ->weight('bold'),

                        TextEntry::make('recommendation.batas_waktu')
                            ->label('Batas Waktu TL')
                            ->date('d M Y')
                            ->color(fn ($record) => 
                                ($record->recommendation?->batas_waktu && $record->recommendation->batas_waktu->isPast()) ? 'danger' : 'info'
                            ),

                        TextEntry::make('recommendation.status')
                            ->label('Status Final Rekomendasi')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'belum_ditindaklanjuti' => 'Belum',
                                'dalam_proses'          => 'Proses',
                                'selesai'               => 'Selesai',
                                default                 => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'belum_ditindaklanjuti' => 'danger',
                                'dalam_proses'          => 'warning',
                                'selesai'               => 'success',
                                default                 => 'gray',
                            }),
                    ]),

                // ── STATUS & PROGRESS REALISASI ───────────────────────────────
                Section::make('Status & Progress Realisasi')
                    ->icon('heroicon-o-chart-bar')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('status_verifikasi')
                            ->label('Status Verifikasi TL')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                'berjalan'            => 'Sedang Berjalan',
                                'lunas'               => 'Lunas / Selesai',
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

                        TextEntry::make('total_terbayar')
                            ->label('Sudah Realisasi')
                            ->money('IDR')
                            ->weight('bold')
                            ->color('success'),

                        TextEntry::make('sisa_belum_bayar')
                            ->label('Sisa Kewajiban')
                            ->money('IDR')
                            ->weight('bold')
                            ->color(fn($state) => $state > 0 ? 'danger' : 'gray'),

                        TextEntry::make('progress_persen')
                            ->label('Persentase')
                            ->getStateUsing(fn ($record) => round($record->progress() ?? 0, 2) . '%')
                            ->badge()
                            ->color(fn ($record) => match (true) {
                                $record->progress() >= 100 => 'success',
                                $record->progress() > 0    => 'warning',
                                default                    => 'danger',
                            }),

                        TextEntry::make('jenis_penyelesaian')
                            ->label('Metode Penyelesaian')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'setor_kas'              => 'Setor ke Kas',
                                'pengembalian_barang'    => 'Pengembalian Aset',
                                'perbaikan_administrasi' => 'Administrasi',
                                'cicilan'                => 'Skema Cicilan',
                                default                  => $state ?? '-',
                            }),

                        IconEntry::make('is_cicilan')
                            ->label('Sistem Cicilan')
                            ->boolean(),
                    ]),

                // ── DETAIL CICILAN (DYNAMIC VISIBILITY) ───────────────────────
                Section::make('Detail Skema Cicilan')
                    ->icon('heroicon-o-calendar-days')
                    ->description('Informasi rencana dan realisasi tenor cicilan.')
                    ->columns(3)
                    ->visible(fn ($record) => (bool) $record->is_cicilan)
                    ->schema([
                        TextEntry::make('nilai_per_cicilan_rencana')
                            ->label('Rencana per Cicilan')
                            ->money('IDR'),

                        TextEntry::make('jumlah_cicilan_rencana')
                            ->label('Target Tenor')
                            ->suffix(' kali bayar'),

                        TextEntry::make('jumlah_cicilan_realisasi')
                            ->label('Realisasi Tenor')
                            ->suffix(' kali bayar')
                            ->weight('bold')
                            ->color('success'),

                        TextEntry::make('tanggal_mulai_cicilan')
                            ->label('Mulai Tanggal')
                            ->date('d M Y'),

                        TextEntry::make('tanggal_jatuh_tempo')
                            ->label('Deadline Akhir')
                            ->date('d M Y')
                            ->color('danger'),
                    ]),

                // ── VERIFIKASI & CATATAN ──────────────────────────────────────
                Section::make('Audit Trail & Catatan')
                    ->icon('heroicon-o-check-badge')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('catatan_tl')
                            ->label('Uraian Tindak Lanjut (Auditi)')
                            ->columnSpanFull()
                            ->prose(),

                        TextEntry::make('verifikator.name')
                            ->label('Verifikator')
                            ->default('Belum diperiksa'),

                        TextEntry::make('diverifikasi_pada')
                            ->label('Waktu Verifikasi')
                            ->dateTime('d M Y, H:i')
                            ->default('-'),

                        TextEntry::make('catatan_verifikasi')
                            ->label('Catatan Hasil Verifikasi (Auditor)')
                            ->columnSpanFull()
                            ->color('warning')
                            ->prose(),
                    ]),

                // ── LAMPIRAN BUKTI ────────────────────────────────────────────
                Section::make('Lampiran Bukti')
                    ->icon('heroicon-o-paper-clip')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('attachments')
                            ->label('')
                            ->hidden(fn($record) => $record->attachments->isEmpty())
                            ->schema([
                                TextEntry::make('jenis_bukti')
                                    ->label('Kategori')
                                    ->formatStateUsing(fn ($state) =>
                                        Attachment::JENIS_BUKTI['tindak_lanjut'][$state]
                                            ?? ucfirst(str_replace('_', ' ', $state ?? '-'))
                                    )
                                    ->badge(),

                                TextEntry::make('file_name')
                                    ->label('Nama File')
                                    ->limit(30),

                                TextEntry::make('file_url')
                                    ->label('Aksi')
                                    ->formatStateUsing(fn () => 'Buka Dokumen')
                                    ->url(fn ($record) => $record->file_url)
                                    ->openUrlInNewTab()
                                    ->color('primary')
                                    ->icon('heroicon-m-arrow-top-right-on-square'),
                            ])
                            ->columns(3),
                        
                        TextEntry::make('no_attachments')
                            ->label('')
                            ->visible(fn($record) => $record->attachments->isEmpty())
                            ->getStateUsing(fn() => 'Tidak ada lampiran bukti yang diunggah.')
                            ->color('gray')
                    ]),
            ]);
    }
}