<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_program_id')
                  ->constrained()->cascadeOnDelete();
            $table->foreignId('unit_diperiksa_id')
                  ->constrained('unit_diperiksas')->cascadeOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->foreignId('ketua_tim_id')
                  ->constrained('users')->restrictOnDelete();
            $table->string('nama_tim', 50)->nullable()
                  ->comment('Tim I | Tim II | Tim III');
            $table->string('nomor_surat', 100)
                  ->nullable()
                  ->comment('Nomor surat penugasan audit');
            $table->string('status', 20)->default('draft')
                  ->comment('draft | berjalan | selesai');
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
 
            // Filament list filters
            $table->index(['audit_program_id', 'status'], 'assign_program_status_idx');
            $table->index(['unit_diperiksa_id', 'status'], 'assign_unit_status_idx');
            // Date-range filter (PKPT schedule view)
            $table->index(['tanggal_mulai', 'tanggal_selesai'], 'assign_date_range_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_assignments');
    }
};
