<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Str;

class RekapTemuanExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $lhps;

    public function __construct($lhps)
    {
        $this->lhps = $lhps;
    }

    public function collection()
    {
        $data = collect();
        $no = 1;

        foreach ($this->lhps as $lhp) {
            $lhpTotalTemuan = 0;
            $lhpTotalTL = 0;
            $lhpTotalSetoran = 0;

            foreach ($lhp->temuans as $temuan) {
                $lhpTotalTemuan += (float) $temuan->nilai_temuan;

                foreach ($temuan->recommendations as $indexRekom => $rekom) {
                    
                    // Kumpulkan baris tindak lanjut (sama dengan logika PDF)
                    $displayRows = [];
                    foreach ($rekom->tindakLanjuts as $tl) {
                        if ($tl->is_cicilan) {
                            foreach ($tl->cicilans as $c) {
                                $displayRows[] = [
                                    'tanggal' => $c->tanggal_bayar?->format('d/m/Y'),
                                    'nilai_tl' => (float) $c->nilai_bayar,
                                    'setoran' => ($c->status === 'diterima') ? (float) $c->nilai_bayar : 0,
                                    'keterangan' => "Cicilan Ke-" . $c->ke,
                                    'status' => ($c->status === 'diterima') ? 'VALID' : 'PROSES'
                                ];
                            }
                        } else {
                            $displayRows[] = [
                                'tanggal' => $tl->created_at->format('d/m/Y'),
                                'nilai_tl' => $rekom->isUang() ? (float) $tl->nilai_tindak_lanjut : 0,
                                'setoran' => ($tl->status_verifikasi === 'lunas' && $rekom->isUang()) ? (float) $tl->nilai_tindak_lanjut : 0,
                                'keterangan' => $tl->catatan_tl ?? ($rekom->isUang() ? 'Tunai' : 'Barang/Admin'),
                                'status' => ($tl->status_verifikasi === 'lunas') ? 'VALID' : 'PROSES'
                            ];
                        }
                    }

                    // Jika tidak ada tindak lanjut, buat satu baris kosong
                    if (empty($displayRows)) {
                        $displayRows[] = [
                            'tanggal' => '-', 'nilai_tl' => 0, 'setoran' => 0, 'keterangan' => 'Belum ada TL', 'status' => '-'
                        ];
                    }

                    $totalSetoranRekom = collect($displayRows)->sum('setoran');
                    $lhpTotalTL += collect($displayRows)->sum('nilai_tl');
                    $lhpTotalSetoran += $totalSetoranRekom;

                    foreach ($displayRows as $indexRow => $row) {
                        $data->push([
                            'no' => ($indexRekom == 0 && $indexRow == 0) ? $no : '',
                            'unit' => ($indexRekom == 0 && $indexRow == 0) ? $lhp->irban . " - " . $lhp->nomor_lhp : '',
                            'temuan' => ($indexRekom == 0 && $indexRow == 0) ? $temuan->kondisi : '',
                            'kode_tmd' => ($indexRekom == 0 && $indexRow == 0) ? $temuan->kodeTemuan?->kode : '',
                            'rekom' => ($indexRow == 0) ? $rekom->uraian_rekom : '',
                            'kode_rek' => ($indexRow == 0) ? ($rekom->kodeRekomendasi?->kode ?? '-') : '',
                            'nilai_temuan' => ($indexRow == 0) ? (float)$temuan->nilai_temuan : 0,
                            'tgl_bayar' => $row['tanggal'],
                            'nilai_tl' => $row['nilai_tl'],
                            'setoran' => $row['setoran'],
                            'sisa' => ($indexRow == count($displayRows) - 1) ? max(0, (float)$temuan->nilai_temuan - $totalSetoranRekom) : 0,
                        ]);
                    }
                }
                $no++;
            }

            // Tambahkan Baris Subtotal Per LHP
            $data->push([
                'no' => '', 'unit' => 'JUMLAH PER LHP', 'temuan' => $lhp->nomor_lhp, 'kode_tmd' => '', 'rekom' => '', 'kode_rek' => '',
                'nilai_temuan' => $lhpTotalTemuan,
                'tgl_bayar' => '',
                'nilai_tl' => $lhpTotalTL,
                'setoran' => $lhpTotalSetoran,
                'sisa' => max(0, $lhpTotalTemuan - $lhpTotalSetoran)
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'NO', 'UNIT / NOMOR LHP', 'TEMUAN (KONDISI)', 'KODE TMD', 'REKOMENDASI', 'KODE REK',
            'NILAI TEMUAN', 'TGL BAYAR', 'NILAI TL', 'SETORAN (VALID)', 'SISA'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D9D9D9']]],
        ];
    }
}