<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| LHP Statistik Cache
|--------------------------------------------------------------------------
| Cache kalkulasi untuk halaman detail LHP & laporan audit.
|
| Diisi otomatis oleh:
| - Observer
| - LhpStatistikService
|
| Tujuan:
| - Menghindari JOIN berat dari tabel temuan/rekomendasi
| - Mempercepat dashboard monitoring tindak lanjut
| - Menyediakan statistik cepat untuk laporan audit
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lhp_statistik', function (Blueprint $table) {

            $table->id();
            $table->foreignId('lhp_id')
                ->unique()
                ->constrained('lhps')
                ->cascadeOnDelete()
                ->index();
            $table->unsignedInteger('total_temuan')->default(0);
            $table->unsignedInteger('total_rekomendasi')->default(0);
            $table->decimal('total_kerugian_negara', 18, 2)->default(0);
            $table->decimal('total_kerugian_daerah', 18, 2)->default(0);
            $table->decimal('total_kerugian_desa', 18, 2)->default(0);
            $table->decimal('total_kerugian_bos_blud', 18, 2)->default(0);

            $table->decimal('total_kerugian', 18, 2)
                ->default(0)
                ->comment('Total seluruh jenis kerugian');
            $table->decimal('total_nilai_tl_selesai', 18, 2)
                ->default(0)
                ->comment('Total nilai yang sudah ditindaklanjuti');
            $table->decimal('total_sisa_kerugian', 18, 2)
                ->default(0)
                ->comment('total_kerugian - total_nilai_tl_selesai');
            $table->unsignedInteger('rekom_belum')->default(0);
            $table->unsignedInteger('rekom_proses')->default(0);
            $table->unsignedInteger('rekom_selesai')->default(0);
            $table->unsignedInteger('rekom_uang_total')->default(0)
                ->comment('Jumlah rekomendasi jenis uang');
            $table->unsignedInteger('rekom_uang_selesai')->default(0)
                ->comment('Jumlah rekomendasi uang yang sudah selesai');
            $table->unsignedInteger('rekom_nonutang_total')->default(0)
                ->comment('Jumlah rekomendasi barang + administrasi');
            $table->unsignedInteger('rekom_nonutang_selesai')->default(0)
                ->comment('Jumlah rekomendasi non-uang yang sudah selesai');
 

            $table->decimal('persen_selesai', 5, 2)
                ->default(0)
                ->comment('rekom_selesai / total_rekomendasi * 100');
            $table->decimal('persen_selesai_nilai', 5, 2)->default(0)
                ->comment(
                    'Persentase nilai rupiah. ' .
                    'nilai_tl_selesai (uang) / nilai_rekom (uang) × 100. ' .
                    'Include cicilan karena nilai_tl_selesai di-cascade dari cicilan.'
                );
            $table->decimal('persen_selesai_gabungan', 5, 2)->default(0)
                ->comment(
                    'Metrik utama dashboard. ' .
                    'Ada rekom uang: 80% × persen_nilai + 20% × persen_count. ' .
                    'Semua non-uang: 100% × persen_count.'
                );
            $table->timestamp('dihitung_pada')
                ->nullable()
                ->comment('Waktu terakhir statistik dihitung ulang');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lhp_statistik');
    }
};