<?php

namespace Database\Seeders;

use App\Models\UnitDiperiksa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitDiperiksaSeeder extends Seeder
{
    public function run(): void
    {
        // Data Kecamatan di Kabupaten Rembang
        $kecamatan = [
            'Rembang', 'Lasem', 'Pancur', 'Kragan', 'Sluke', 
            'Sarang', 'Sedan', 'Pamotan', 'Sale', 'Gunem', 
            'Bulu', 'Sumber', 'Kaliori', 'Sulang'
        ];

        // Kategori Unit
        $kategori = ['Puskesmas', 'Sekolah', 'Kantor Desa', 'UPT Dinas', 'Pasar'];

        $data = [];

        for ($i = 1; $i <= 100; $i++) {
            $namaKec = $kecamatan[array_rand($kecamatan)];
            $kat = $kategori[array_rand($kategori)];
            
            $data[] = [
                'nama_unit'      => "$kat $namaKec " . rand(1, 5),
                'kategori'       => $kat,
                'nama_kecamatan' => $namaKec,
                'alamat'         => "Jl. Raya $namaKec No. " . rand(1, 150) . ", Rembang",
                'telepon'        => '0295' . rand(100000, 999999),
                'keterangan'     => 'Unit pemeriksaan rutin wilayah ' . $namaKec,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        // Insert menggunakan chunk agar lebih ringan jika datanya ditambah lebih banyak
        foreach (array_chunk($data, 50) as $chunk) {
            UnitDiperiksa::insert($chunk);
        }
    }
}