<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindak_lanjut_cicilans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tindak_lanjut_id')
                ->constrained('tindak_lanjuts')
                ->cascadeOnDelete();
            
            $table->unsignedInteger('ke') // Diubah ke Integer (lebih aman untuk banyak cicilan)
                ->comment('Nomor urut cicilan');
                
            $table->decimal('nilai_bayar', 18, 2);
            $table->date('tanggal_bayar');
            // $table->date('tanggal_jatuh_tempo_cicilan')->nullable();
            
            $table->string('nomor_bukti', 100)->nullable();
            $table->text('keterangan')->nullable();
            
            $table->enum('jenis_bayar', [
                'tunai', 'transfer', 'setor_kas', 
                'pengembalian_barang', 'perbaikan_fisik', 'lainnya'
            ])->default('setor_kas');

            $table->decimal('nilai_bayar_negara', 18, 2)->default(0);
            $table->decimal('nilai_bayar_daerah', 18, 2)->default(0);
            $table->decimal('nilai_bayar_desa', 18, 2)->default(0);
            $table->decimal('nilai_bayar_bos_blud', 18, 2)->default(0);
            
            $table->string('status', 30)->default('menunggu_verifikasi');

            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->text('catatan_verifikasi')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes(); // Kolom deleted_at aktif

            // ── PERBAIKAN UTAMA: Unique Index ────────────────────────────────
            // Dengan memasukkan 'deleted_at' ke dalam unique, data yang di-soft-delete
            // tidak akan memblokir nomor urut 'ke' yang sama untuk data baru.
            $table->unique(
                ['tindak_lanjut_id', 'ke', 'deleted_at'], 
                'cicilan_tl_ke_unique'
            );

            // ── Indexing untuk Performa Monitoring LHP Rembang ───────────────
            $table->index(['tindak_lanjut_id', 'status'], 'cicilan_monitor_idx');
            $table->index('tanggal_bayar', 'cicilan_tgl_bayar_idx');
            // $table->index('tanggal_jatuh_tempo_cicilan', 'cicilan_jt_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut_cicilans');
    }
};