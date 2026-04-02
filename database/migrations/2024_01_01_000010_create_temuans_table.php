<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temuans', function (Blueprint $table) {

            $table->id();
            $table->foreignId('lhp_id')
                ->constrained('lhps')
                ->cascadeOnDelete();
            $table->foreignId('kode_temuan_id')
                ->nullable()
                ->constrained('kode_temuans')
                ->nullOnDelete();
            $table->text('kondisi')
                ->comment('What happened — kondisi yang tidak sesuai kriteria');
            $table->text('sebab')
                ->nullable()
                ->comment('Root cause');
            $table->text('akibat')
                ->nullable()
                ->comment('Dampak dari kondisi');
            $table->decimal('nilai_temuan', 18, 2)
                ->default(0);
            $table->decimal('nilai_kerugian_negara', 18, 2)
                ->default(0)
                ->comment('Kerugian Negara');
            $table->decimal('nilai_kerugian_daerah', 18, 2)
                ->default(0)
                ->comment('Kerugian Daerah');
            $table->decimal('nilai_kerugian_desa', 18, 2)
                ->default(0)
                ->comment('Kerugian Desa');
            $table->decimal('nilai_kerugian_bos_blud', 18, 2)
                ->default(0)
                ->comment('Kerugian BOS / BLUD');
            $table->string('status_tl', 30)
                ->default('belum_ditindaklanjuti')
                ->comment('belum_ditindaklanjuti | dalam_proses | selesai');

            $table->string('nama_barang')->nullable();
            $table->string('jumlah_barang')->nullable();
            $table->text('kondisi_barang')->nullable();
            $table->text('lokasi_barang')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('lhp_id');
            $table->index('kode_temuan_id');

            // Statistik query
            $table->index(
                ['lhp_id', 'status_tl', 'deleted_at'],
                'temuan_lhp_status_deleted_idx'
            );

            // Query laporan kerugian
            $table->index(
                ['lhp_id', 'nilai_temuan'],
                'temuan_lhp_nilai_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temuans');
    }
};