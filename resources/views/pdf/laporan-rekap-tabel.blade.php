<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <style>
        @page { margin: 0.5cm; size: landscape; }
        body { font-family: 'Helvetica', sans-serif; font-size: 9px; line-height: 1.4; color: #333; }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2 { margin: 0; font-size: 14px; }
        .header p { margin: 2px 0; font-size: 10px; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 4px; word-wrap: break-word; vertical-align: top; }
        th { background-color: #d9d9d9; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 8.5px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer-lhp { font-weight: bold; background-color: #eeeeee; }

        .invalid { color: red; font-style: italic; }
        td.wrap { white-space: normal; }
        td.no-left-border { border-left: none; }
        td.setoran, td.sisa, td.nilai-tl { min-width: 80px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>DAFTAR TEMUAN PEMERIKSAAN & PELAKSANAAN TINDAK LANJUT</h2>
        <p>INSPEKTORAT KABUPATEN REMBANG</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="12%">NAMA UNIT / LHP</th>
                <th width="14%">TEMUAN (KONDISI)</th>
                <th width="5%">KODE TMD</th>
                <th width="14%">REKOMENDASI</th>
                <th width="5%">KODE REK</th>
                <th width="9%">NILAI TEMUAN</th>
                <th width="8%">TGL BAYAR / KET</th>
                <th width="10%">NILAI TL (Rp/Barang)</th>
                <th width="10%">SETORAN (VALID)</th>
                <th width="10%">SISA (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($lhps as $lhp)
                @php 
                    $lhpTotalTemuan = 0;
                    $lhpTotalTL = 0;
                    $lhpTotalSetoran = 0;
                @endphp

                @foreach($lhp->temuans as $temuan)
                    @php $lhpTotalTemuan += $temuan->nilai_temuan; @endphp
                    
                    @foreach($temuan->recommendations as $indexRekom => $rekom)
                        @php 
                            $displayRows = [];
                            foreach($rekom->tindakLanjuts as $tl) {
                                if ($tl->is_cicilan) {
                                    foreach($tl->cicilans as $c) {
                                        $displayRows[] = [
                                            'tanggal' => $c->tanggal_bayar?->format('d/m/Y'),
                                            'nilai_tl' => $c->nilai_bayar,
                                            'setoran' => ($c->status === 'diterima') ? $c->nilai_bayar : 0,
                                            'keterangan' => "Cicilan Ke-" . $c->ke,
                                            'is_valid' => ($c->status === 'diterima')
                                        ];
                                    }
                                } else {
                                    $displayRows[] = [
                                        'tanggal' => $tl->created_at->format('d/m/Y'),
                                        'nilai_tl' => ($rekom->isUang()) ? $tl->nilai_tindak_lanjut : 0,
                                        'setoran' => ($tl->status_verifikasi === 'lunas' && $rekom->isUang()) ? $tl->nilai_tindak_lanjut : 0,
                                        'keterangan' => $tl->catatan_tl ?? ($rekom->isUang() ? 'Bayar Tunai' : 'Penyerahan Barang/Admin'),
                                        'is_valid' => ($tl->status_verifikasi === 'lunas')
                                    ];
                                }
                            }
                            
                            $totalSetoranRekom = collect($displayRows)->sum('setoran');
                            $lhpTotalTL += collect($displayRows)->sum('nilai_tl');
                            $lhpTotalSetoran += $totalSetoranRekom;
                        @endphp

                        @forelse($displayRows as $indexRow => $row)
                        <tr>
                            @if($indexRekom == 0 && $indexRow == 0)
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="wrap">
                                    <strong>
                                        {{ $lhp->auditAssignment?->unitDiperiksa?->nama_unit ?? 'TIDAK ADA UNIT' }}
                                    </strong><br>
                                    {{ $lhp->nomor_lhp }}
                                </td>
                                <td class="wrap">{{ $temuan->kondisi }}</td>
                                <td class="text-center">{{ $temuan->kodeTemuan?->kode }}</td>
                            @else
                                <td colspan="4" class="no-left-border"></td>
                            @endif

                            <td class="wrap">{{ $indexRow == 0 ? $rekom->uraian_rekom : '' }}</td>
                            <td class="text-center">{{ $indexRow == 0 ? ($rekom->kodeRekomendasi?->kode ?? '-') : '' }}</td>
                            <td class="text-right">{{ $indexRow == 0 ? number_format($temuan->nilai_temuan, 0, ',', '.') : '' }}</td>

                            <td class="text-center">{{ $row['tanggal'] }}</td>
                            <td class="text-right nilai-tl">
                                @if($row['nilai_tl'] > 0)
                                    {{ number_format($row['nilai_tl'], 0, ',', '.') }}
                                @else
                                    <small>{{ \Illuminate\Support\Str::limit($row['keterangan'], 25) }}</small>
                                @endif
                            </td>
                            <td class="text-right setoran {{ !$row['is_valid'] ? 'invalid' : '' }}">
                                @if($row['is_valid'])
                                    {{ $row['setoran'] > 0 ? number_format($row['setoran'], 0, ',', '.') : 'LUNAS' }}
                                @else
                                    0
                                @endif
                            </td>
                            <td class="text-right sisa">
                                @if($loop->last)
                                    <strong>{{ number_format(max(0, $temuan->nilai_temuan - $totalSetoranRekom), 0, ',', '.') }}</strong>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            @if($indexRekom == 0)
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="wrap">
                                    <strong>
                                        {{ $lhp->auditAssignment?->unitDiperiksa?->nama_unit ?? 'TIDAK ADA UNIT' }}
                                    </strong><br>
                                    {{ $lhp->nomor_lhp }}
                                </td>
                                <td class="wrap">{{ $temuan->kondisi }}</td>
                                <td class="text-center">{{ $temuan->kodeTemuan?->kode }}</td>
                            @else
                                <td colspan="4" class="no-left-border"></td>
                            @endif
                            <td class="wrap">{{ $rekom->uraian_rekom }}</td>
                            <td class="text-center">{{ $rekom->kodeRekomendasi?->kode ?? '-' }}</td>
                            <td class="text-right">{{ number_format($temuan->nilai_temuan, 0, ',', '.') }}</td>
                            <td colspan="3" class="text-center invalid">Belum ditindaklanjuti</td>
                            <td class="text-right">{{ number_format($temuan->nilai_temuan, 0, ',', '.') }}</td>
                        </tr>
                        @endforelse
                    @endforeach
                @endforeach

                <tr class="footer-lhp">
                    <td colspan="6" class="text-right">JUMLAH PER LHP : {{ $lhp->nomor_lhp }}</td>
                    <td class="text-right">{{ number_format($lhpTotalTemuan, 0, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($lhpTotalTL, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($lhpTotalSetoran, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format(max(0, $lhpTotalTemuan - $lhpTotalSetoran), 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>