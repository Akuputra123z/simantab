<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Models\TindakLanjut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TindakLanjutController extends Controller
{
    public function store(Request $request, Recommendation $recommendation)
    {
        // 1. Validasi Input Dasar & Logika Sisa Saldo
        $request->validate([
            'tanggal_tl'   => 'required|date',
            'uraian_tl'    => 'required|string|min:10',
            'nilai_setoran' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($recommendation) {
                    // Cek apakah nilai yang diinput melebihi sisa yang ada di database
                    if ($recommendation->isUang() && $value > (float) $recommendation->nilai_sisa) {
                        $fail("Nominal setoran (Rp " . number_format($value) . ") melebihi sisa kewajiban yang ada (Rp " . number_format($recommendation->nilai_sisa) . ").");
                    }
                },
            ],
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // 2. Simpan Data Tindak Lanjut
            $tindakLanjut = new TindakLanjut();
            $tindakLanjut->recommendation_id = $recommendation->id;
            $tindakLanjut->tanggal_tl       = $request->tanggal_tl;
            $tindakLanjut->uraian_tl        = $request->uraian_tl;
            $tindakLanjut->nilai_setoran    = $request->nilai_setoran;
            $tindakLanjut->status_verifikasi = 'proses'; // Default awal
            $tindakLanjut->created_by       = Auth::id();
            $tindakLanjut->save();

            // 3. Update Akumulasi di Tabel Recommendation
            // Kita gunakan updateQuietly agar tidak memicu loop event jika ada
            $newNilaiTl = (float) $recommendation->nilai_tl_selesai + (float) $request->nilai_setoran;
            
            $recommendation->update([
                'nilai_tl_selesai' => $newNilaiTl,
                'status'           => $newNilaiTl >= (float)$recommendation->nilai_rekom 
                                      ? Recommendation::STATUS_SELESAI 
                                      : Recommendation::STATUS_PROSES
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Data tindak lanjut berhasil disimpan dan statistik telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}