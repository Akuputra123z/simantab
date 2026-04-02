<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_programs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_program', 200);
            $table->year('tahun')
                  ->comment('Tahun anggaran PKPT');
            $table->integer('target_assignment')->default(0);
            $table->string('status', 20)->default('draft')
                  ->comment('draft | berjalan | selesai');
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
 
            // Filament table filter: by year + status
            $table->index(['tahun', 'status'], 'program_tahun_status_idx');
            $table->index(['status', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_programs');
    }
};
