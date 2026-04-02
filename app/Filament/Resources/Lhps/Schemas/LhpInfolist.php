<?php

namespace App\Filament\Resources\Lhps\Schemas;

use App\Models\Attachment;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LhpInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Penugasan Audit')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->schema([

                         TextEntry::make('auditAssignment.nomor_surat')
                            ->label('Nomor Surat')
                            ->default('-'),
                        TextEntry::make('auditAssignment.auditProgram.nama_program')
                            ->label('Program Audit')
                            ->default('-'),

                        TextEntry::make('auditAssignment.auditProgram.tahun')
                            ->label('Tahun')
                            ->badge()->color('info')
                            ->default('-'),

                        TextEntry::make('auditAssignment.unitDiperiksa.nama_unit')
                            ->label('Unit Diperiksa')
                            ->default('-'),

                        TextEntry::make('auditAssignment.unitDiperiksa.kategori')
                            ->label('Kategori')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'SKPD'      => 'primary',
                                'Sekolah'   => 'success',
                                'Puskesmas' => 'warning',
                                'Desa'      => 'info',
                                'BLUD'      => 'danger',
                                default     => 'gray',
                            })
                            ->default('-'),

                        TextEntry::make('auditAssignment.ketuaTim.name')
                            ->label('Ketua Tim')->default('-'),

                        TextEntry::make('auditAssignment.nama_tim')
                            ->label('Nama Tim')->default('-'),

                        TextEntry::make('auditAssignment.tanggal_mulai')
                            ->label('Tanggal Mulai')->date('d M Y')->default('-'),

                        TextEntry::make('auditAssignment.tanggal_selesai')
                            ->label('Tanggal Selesai')->date('d M Y')->default('-'),
                    ]),

                Section::make('Informasi LHP')
                    ->icon('heroicon-o-document-text')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('nomor_lhp')
                            ->label('Nomor LHP')
                            ->columnSpanFull()
                            ->weight('bold'),

                        TextEntry::make('tanggal_lhp')
                            ->label('Tanggal LHP')->date('d M Y')->default('-'),

                        TextEntry::make('semester')
                            ->label('Semester')
                            ->formatStateUsing(fn ($state) => "Semester {$state}")
                            ->badge()->color('gray'),

                        TextEntry::make('jenis_pemeriksaan')
                            ->label('Jenis Pemeriksaan')
                            ->badge()->color('primary')->default('-'),

                        TextEntry::make('irban')
                            ->label('Irban')
                            ->badge()->color('info')->default('-'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'draft'          => 'gray',
                                'final'          => 'warning',
                                'ditandatangani' => 'success',
                                default          => 'gray',
                            }),
                    ]),

                // ── LAMPIRAN ──────────────────────────────────────────────────
                // Gunakan RelationManager di ViewLhp agar bisa tambah/edit/hapus.
                // Di sini hanya untuk preview read-only.
                Section::make('Lampiran')
                    ->icon('heroicon-o-paper-clip')
                    ->collapsible()
                    ->collapsed(fn ($record) => $record->attachments->isEmpty())
                    ->headerActions([
                        // Tombol tambah lampiran langsung dari View
                        // (gunakan jika tidak pakai RelationManager terpisah)
                    ])
                    ->schema([
                        RepeatableEntry::make('attachments')
                            ->label('')
                            ->schema([

                                TextEntry::make('jenis_bukti')
                                    ->label('Jenis')
                                    ->formatStateUsing(fn ($state) =>
                                        Attachment::JENIS_BUKTI['lhp'][$state] ?? ucfirst(str_replace('_', ' ', $state ?? '-'))
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

                Section::make('Progress Tindak Lanjut')
                    ->icon('heroicon-o-chart-bar')
                    ->columns(4)
                    ->schema([

                        TextEntry::make('statistik.total_temuan')
                            ->label('Total Temuan')
                            ->badge()->color('danger')->default(0),

                        TextEntry::make('statistik.total_rekomendasi')
                            ->label('Total Rekomendasi')
                            ->badge()->color('warning')->default(0),

                        TextEntry::make('statistik.rekom_selesai')
                            ->label('Rekom Selesai')
                            ->badge()->color('success')->default(0),

                        TextEntry::make('statistik.persen_selesai')
                            ->label('% TL')
                            ->suffix('%')
                            ->badge()
                            ->color(fn ($state) => match (true) {
                                $state >= 100 => 'success',
                                $state > 0    => 'warning',
                                default       => 'danger',
                            })
                            ->default(0),

                        TextEntry::make('statistik.total_kerugian')
                            ->label('Total Kerugian')
                            ->money('IDR')->default(0),

                        TextEntry::make('statistik.total_nilai_tl_selesai')
                            ->label('Sudah Diselesaikan')
                            ->money('IDR')->default(0),

                        TextEntry::make('statistik.total_sisa_kerugian')
                            ->label('Sisa Kerugian')
                            ->money('IDR')->color('danger')->default(0),
                    ]),

                Section::make('Catatan Umum Audit')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        TextEntry::make('catatan_umum')
                            ->label('')
                            ->columnSpanFull()
                            ->default('Belum ada catatan.'),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => ! $record->catatan_umum),

                Section::make('Pembatalan Status')
                    ->icon('heroicon-o-x-circle')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('status_batal_keterangan')
                            ->label('Alasan Pembatalan')
                            ->default('-')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => !$record->status_batal_keterangan),
            ]);
    }
}