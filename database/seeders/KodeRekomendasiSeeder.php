<?php

namespace Database\Seeders;

use App\Models\KodeRekomendasi;
use Illuminate\Database\Seeder;

/**
 * KodeRekomendasiSeeder
 *
 * Sumber: PermenPAN No. 42 Tahun 2011
 *         Lampiran 2.2 — Kode Atribut Rekomendasi
 *
 * Total: 14 kode resmi (kode_numerik 01–14)
 */
class KodeRekomendasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode'         => 'RK-01',
                'kode_numerik' => 1,
                'kategori'     => 'Finansial',
                'deskripsi'    => 'Penyetoran ke kas negara/daerah, kas BUMN/D, dan masyarakat',
            ],
            [
                'kode'         => 'RK-02',
                'kode_numerik' => 2,
                'kategori'     => 'Aset',
                'deskripsi'    => 'Pengembalian barang kepada negara, daerah, BUMN/D, dan masyarakat',
            ],
            [
                'kode'         => 'RK-03',
                'kode_numerik' => 3,
                'kategori'     => 'Fisik',
                'deskripsi'    => 'Perbaikan fisik barang/jasa dalam proses pembangunan atau penggantian barang/jasa oleh rekanan',
            ],
            [
                'kode'         => 'RK-04',
                'kode_numerik' => 4,
                'kategori'     => 'Aset',
                'deskripsi'    => 'Penghapusan barang milik negara/daerah',
            ],
            [
                'kode'         => 'RK-05',
                'kode_numerik' => 5,
                'kategori'     => 'Kepegawaian',
                'deskripsi'    => 'Pelaksanaan sanksi administrasi kepegawaian',
            ],
            [
                'kode'         => 'RK-06',
                'kode_numerik' => 6,
                'kategori'     => 'Administrasi',
                'deskripsi'    => 'Perbaikan laporan dan penertiban administrasi/kelengkapan administrasi',
            ],
            [
                'kode'         => 'RK-07',
                'kode_numerik' => 7,
                'kategori'     => 'Akuntansi',
                'deskripsi'    => 'Perbaikan sistem dan prosedur akuntansi dan pelaporan',
            ],
            [
                'kode'         => 'RK-08',
                'kode_numerik' => 8,
                'kategori'     => 'SDM',
                'deskripsi'    => 'Peningkatan kualitas dan kuantitas sumber daya manusia pendukung sistem pengendalian',
            ],
            [
                'kode'         => 'RK-09',
                'kode_numerik' => 9,
                'kategori'     => 'Regulasi',
                'deskripsi'    => 'Perubahan atau perbaikan prosedur, peraturan dan kebijakan',
            ],
            [
                'kode'         => 'RK-10',
                'kode_numerik' => 10,
                'kategori'     => 'Organisasi',
                'deskripsi'    => 'Perubahan atau perbaikan struktur organisasi',
            ],
            [
                'kode'         => 'RK-11',
                'kode_numerik' => 11,
                'kategori'     => 'Koordinasi',
                'deskripsi'    => 'Koordinasi antar instansi termasuk juga penyerahan penanganan kasus kepada instansi yang berwenang',
            ],
            [
                'kode'         => 'RK-12',
                'kode_numerik' => 12,
                'kategori'     => 'Audit Lanjutan',
                'deskripsi'    => 'Pelaksanaan penelitian oleh tim khusus atau audit lanjutan oleh unit pengawas intern',
            ],
            [
                'kode'         => 'RK-13',
                'kode_numerik' => 13,
                'kategori'     => 'Sosialisasi',
                'deskripsi'    => 'Pelaksanaan sosialisasi',
            ],
            [
                'kode'         => 'RK-14',
                'kode_numerik' => 14,
                'kategori'     => 'Lain-lain',
                'deskripsi'    => 'Lain-lain',
            ],
        ];

        foreach ($data as $item) {
            KodeRekomendasi::updateOrCreate(
                ['kode_numerik' => $item['kode_numerik']],
                array_merge($item, ['is_active' => true])
            );
        }

        $this->command->info('✅ KodeRekomendasiSeeder: 14 kode berhasil di-seed.');
    }
}