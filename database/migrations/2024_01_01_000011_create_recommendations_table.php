<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {

            $table->id();
            $table->foreignId('temuan_id')
                ->constrained('temuans')
                ->cascadeOnDelete();
            $table->foreignId('kode_rekomendasi_id')
                ->nullable()
                ->constrained('kode_rekomendasis')
                ->nullOnDelete();

            $table->text('uraian_rekom');

            $table->decimal('nilai_rekom', 18, 2)
                ->default(0)
                ->comment('Nilai rekomendasi auditor');
            $table->decimal('nilai_tl_selesai', 18, 2)
                ->default(0)
                ->comment('Cache SUM nilai_bayar WHERE status=diterima');
            $table->decimal('nilai_sisa', 18, 2)
                ->default(0)
                ->comment('nilai_rekom - nilai_tl_selesai');
            $table->date('batas_waktu')
                ->nullable()
                ->index()
                ->comment('Deadline tindak lanjut');
            $table->string('status', 30)
                ->default('belum_ditindaklanjuti')
                ->comment('belum_ditindaklanjuti | dalam_proses | selesai');

            $table->string('jenis_rekomendasi')->default('uang');
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
            $table->index(
                ['temuan_id', 'status', 'deleted_at'],
                'rekom_temuan_status_deleted_idx'
            );

            // Monitoring TL
            $table->index(
                ['status', 'batas_waktu'],
                'rekom_status_deadline_idx'
            );

            // Query laporan nilai
            $table->index(
                ['temuan_id', 'nilai_rekom'],
                'rekom_temuan_nilai_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};