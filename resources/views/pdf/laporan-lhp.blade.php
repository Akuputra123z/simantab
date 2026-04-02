<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Monitoring TL - {{ $record->nomor_lhp }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; line-height: 1.4; color: #000; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 6px; margin-bottom: 12px; }
        .kop-instansi { font-size: 13px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .kop-sub { font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 2px 0; }
        .alamat { font-size: 9px; margin-top: 3px; }
        .title { text-align: center; font-size: 12px; font-weight: bold; text-decoration: underline; margin: 10px 0 12px; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 2px 4px; font-size: 10px; }
        .stat-box { width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px; }
        .stat-box td { border: 1px solid #000; padding: 5px 8px; text-align: center; font-size: 10px; }
        .stat-box .label { background: #f0f0f0; font-weight: bold; font-size: 9px; }
        .stat-box .value { font-weight: bold; font-size: 11px; }
        .table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 4px; }
        .table th, .table td { border: 1px solid #000; padding: 5px 4px; word-wrap: break-word; vertical-align: top; font-size: 9px; }
        .table th { background-color: #d9d9d9; text-align: center; font-weight: bold; text-transform: uppercase; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }
        .rekom-item { margin-bottom: 8px; padding-bottom: 5px; border-bottom: 0.5px solid #eee; }
        .rekom-item:last-child { border-bottom: none; }
        .tl-item { font-size: 8px; color: #444; margin-top: 3px; padding-left: 8px; border-left: 2px solid #ccc; font-style: italic; margin-bottom: 2px; }
        .footer-box { float: right; width: 220px; text-align: center; margin-top: 20px; }
        .space-ttd { height: 50px; }
    </style>
</head>
<body onload="window.print()">

<div class="header">
    <div class="kop-instansi">Pemerintah Kabupaten Rembang</div>
    <div class="kop-sub">Inspektorat Daerah</div>
    <div class="alamat">Jl. Gatot Subroto No. 8, Rembang, Jawa Tengah 59211</div>
</div>

<div class="title">Laporan Monitoring Tindak Lanjut Hasil Pemeriksaan</div>

<table class="info-table">
    <tr>
        <td width="20%"><strong>Nomor LHP</strong></td>
        <td width="2%">:</td>
        <td width="28%">{{ $record->nomor_lhp }}</td>
        <td width="20%"><strong>Tanggal Cetak</strong></td>
        <td width="2%">:</td>
        <td width="28%">{{ now()->translatedFormat('d F Y') }}</td>
    </tr>
</table>

@php
    $stat = $record->statistik;
    $persen = $stat->persen_selesai_gabungan ?? 0;
@endphp

<table class="stat-box">
    <tr>
        <td class="label">Total Temuan</td><td class="label">Total Rekom</td><td class="label">Selesai</td><td class="label">Proses</td><td class="label">Belum TL</td><td class="label">% Progres</td>
    </tr>
    <tr>
        <td class="value">{{ $stat->total_temuan ?? 0 }}</td>
        <td class="value">{{ $stat->total_rekomendasi ?? 0 }}</td>
        <td class="value">{{ $stat->rekom_selesai ?? 0 }}</td>
        <td class="value">{{ $stat->rekom_proses ?? 0 }}</td>
        <td class="value">{{ $stat->rekom_belum ?? 0 }}</td>
        <td class="value">{{ number_format($persen, 1, ',', '.') }}%</td>
    </tr>
</table>

<table class="table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="28%">Kondisi / Temuan</th>
            <th width="38%">Rekomendasi & Riwayat Cicilan</th>
            <th width="15%">Nilai Temuan</th>
            <th width="15%">Realisasi</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $totalNilai = 0; 
            $totalRealisasi = 0; 
        @endphp

        @forelse($record->temuans as $index => $temuan)
            @php
                $currentNilaiTemuan = (float) $temuan->nilai_temuan;
                $totalNilai += $currentNilaiTemuan;
                
                // Ambil semua cicilan yang sudah DITERIMA untuk kalkulasi realisasi
                $subTotalTl = $temuan->recommendations->flatMap->tindakLanjuts
                    ->flatMap->cicilans->where('status', 'diterima')->sum('nilai_bayar');
                
                // Tambahkan setoran non-cicilan jika ada
                $subTotalTl += $temuan->recommendations->flatMap->tindakLanjuts
                    ->where('is_cicilan', false)->where('status_verifikasi', '!=', 'ditolak')->sum('total_terbayar');
                
                $totalRealisasi += (float) $subTotalTl;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $temuan->kondisi }}</strong><br>
                    <span style="font-size: 8px; color: #666;">KODE: {{ $temuan->kodeTemuan?->kode ?? '-' }}</span>
                </td>
                <td>
                    @foreach($temuan->recommendations as $rekom)
                        <div class="rekom-item">
                            <strong>R{{ $loop->iteration }}:</strong> {{ $rekom->uraian_rekom }}
                            
                            {{-- Tampilkan Riwayat dari TindakLanjutCicilan --}}
                            @php 
                                $riwayatCicilan = $rekom->tindakLanjuts->flatMap->cicilans
                                    ->where('status', 'diterima')
                                    ->sortBy('tanggal_bayar');
                            @endphp

                            @forelse($riwayatCicilan as $cicilan)
                                <div class="tl-item">
                                    • Cicilan Ke-{{ $cicilan->ke }} ({{ $cicilan->tanggal_bayar?->format('d/m/Y') }}): 
                                    {{ $cicilan->keterangan ?? 'Setoran' }} 
                                    <span class="font-bold">(Rp{{ number_format($cicilan->nilai_bayar, 0, ',', '.') }})</span>
                                </div>
                            @empty
                                {{-- Cek jika ada TL lunas tanpa cicilan --}}
                                @php $tlLunas = $rekom->tindakLanjuts->where('is_cicilan', false)->where('status_verifikasi', '!=', 'ditolak'); @endphp
                                @foreach($tlLunas as $lunas)
                                    <div class="tl-item">
                                        • Setoran Lunas: {{ $lunas->catatan_tl }} 
                                        <span class="font-bold">(Rp{{ number_format($lunas->total_terbayar, 0, ',', '.') }})</span>
                                    </div>
                                @endforeach
                                
                                @if($tlLunas->isEmpty())
                                    <div class="tl-item" style="color: #999;">• Belum ada tindak lanjut.</div>
                                @endif
                            @endforelse
                        </div>
                    @endforeach
                </td>
                <td class="text-right">Rp {{ number_format($currentNilaiTemuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($subTotalTl, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center">Data tidak ditemukan.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right font-bold">TOTAL KESELURUHAN</td>
            <td class="text-right font-bold">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
            <td class="text-right font-bold">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" class="text-right font-bold" style="color: red;">SISA BELUM SELESAI</td>
            <td colspan="2" class="text-right font-bold" style="color: red;">
                Rp {{ number_format(max(0, $totalNilai - $totalRealisasi), 0, ',', '.') }}
            </td>
        </tr>
    </tfoot>
</table>

<div class="footer-box">
    <p>Rembang, {{ now()->translatedFormat('d F Y') }}</p>
    <p>Inspektur Kabupaten Rembang,</p>
    <div class="space-ttd"></div>
    <p class="font-bold"><u>( ........................................... )</u></p>
    <p>NIP. .............................................</p>
</div>

</body>
</html>