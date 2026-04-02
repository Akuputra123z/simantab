<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Data nyata: '700/009/001P/PKPT.2023', 'Pemeriksaan Reguler Irban I', 'SEMESTER I'

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lhps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_assignment_id')
                  ->constrained('audit_assignments')->cascadeOnDelete();
            $table->string('nomor_lhp', 100)->unique()
                  ->comment('e.g. 700/009/001P/PKPT.2023');
            $table->date('tanggal_lhp')->nullable();
            $table->unsignedTinyInteger('semester')
                  ->comment('1 | 2 — PermenPAN 42/2011 Bab III');
            $table->string('jenis_pemeriksaan', 50)->nullable()
                  ->comment('Reguler | Khusus | Investigasi | ADTT');
            $table->string('irban', 50)->nullable()
                  ->comment('Irban I | Irban II | Irban III');
            $table->text('catatan_umum')->nullable()
                  ->comment('Ringkasan/simpulan audit — ditampilkan di halaman detail LHP');
            $table->string('status', 30)->default('draft')
                  ->comment('draft | final | ditandatangani');
               $table->text('status_batal_keterangan')->nullable();
            $table->unsignedBigInteger('status_batal_user_id')->nullable();
            $table->timestamp('status_batal_at')->nullable();
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
 
            // Filament: date-range + semester filter on list page
            $table->index(['tanggal_lhp', 'semester'], 'lhp_tanggal_semester_idx');
            // Filament: status badge column filter
            $table->index('status', 'lhp_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lhps');
    }
};
