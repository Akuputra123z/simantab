<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Riwayat laporan audit yang sudah digenerate
//
// Dibutuhkan untuk:
//   - Generate laporan audit otomatis (PDF)
//   - Simpan history laporan yang sudah dicetak/dikirim
//   - Snapshot data statistik saat laporan dibuat

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lhp_reports', function (Blueprint $table) {
            $table->id();
            
            // Identitas
            $table->foreignId('lhp_id')->constrained('lhps')->cascadeOnDelete();
            $table->string('nomor_laporan')->unique(); // Nomor Agenda/Surat Keluar Laporan
            $table->string('judul_laporan');
            $table->string('jenis_laporan'); // semester_1, semester_2, tahunan, khusus
            
            // Metadata untuk Query Cepat (Tanpa bongkar JSON)
            $table->decimal('total_kerugian_snapshot', 18, 2)->default(0);
            $table->float('persen_selesai_snapshot')->default(0);
            
            // File & Data (The Core)
            $table->string('file_path')->nullable(); // Path PDF di Storage
            $table->json('snapshot_data'); // Seluruh hierarki LHP -> Temuan -> Rekom -> TL
            
            // Audit Trail
            $table->timestamp('generated_at');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexing untuk performa di Rembang jika data sudah ribuan
            $table->index(['jenis_laporan', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lhp_reports');
    }
};
