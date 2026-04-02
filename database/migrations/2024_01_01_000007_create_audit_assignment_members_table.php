<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Many-to-many: users <-> audit_assignments
// Dibutuhkan untuk: identitas LHP → daftar anggota tim dalam laporan audit

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_assignment_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_assignment_id')
                  ->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')->cascadeOnDelete();
            $table->string('jabatan_tim', 50)->nullable()
                  ->comment('Ketua | Anggota | Pengendali Teknis');
            $table->timestamps();
 
            // Prevent duplicate member entry per assignment
            $table->unique(
                ['audit_assignment_id', 'user_id'],
                'member_assign_user_unique'
            );
            // Reverse lookup: all assignments for a user
            $table->index('user_id', 'member_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_assignment_members');
    }
};
