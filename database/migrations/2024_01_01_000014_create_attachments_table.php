<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
 
            // ── File metadata ─────────────────────────────────────────────
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 100)->nullable()
                  ->comment('MIME type, e.g. application/pdf | image/jpeg');
            $table->unsignedBigInteger('file_size')->nullable()
                  ->comment('Size in bytes');
 
            // ── Polymorphic relation ──────────────────────────────────────
            // morphs() creates: attachable_type (varchar 255), attachable_id (bigint unsigned)
            // and a composite index on both — do NOT add a manual index for these columns
            $table->morphs('attachable');
 
            // ── Grouping & display ────────────────────────────────────────
            $table->string('jenis_bukti', 100)->nullable()->index()
                  ->comment(
                      'temuan: foto_kondisi|dokumen_pendukung|ba_pemeriksaan | ' .
                      'tindak_lanjut: bukti_setor|bukti_pengembalian|ba_verifikasi|surat_keterangan | ' .
                      'lhp: draft_lhp|lhp_final|surat_pengantar'
                  );
            $table->unsignedSmallInteger('urutan')->default(0)
                  ->comment('Display order — supports Filament reorder (0 = first)');
            $table->string('keterangan', 255)->nullable();
            $table->enum('visibilitas', ['internal', 'publik'])->default('internal')
                  ->comment('internal = inspektorat only | publik = OPD can view');
 
            $table->foreignId('uploaded_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
 
            // "All attachments of type X for entity Y" — most common query
            $table->index(
                ['attachable_type', 'attachable_id', 'jenis_bukti'],
                'attachable_jenis_idx'
            );
            // "Show attachments for entity Y ordered" — Filament file manager
            $table->index(
                ['attachable_type', 'attachable_id', 'urutan'],
                'attachable_urutan_idx'
            );
            $table->index('uploaded_by', 'attach_uploaded_by_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
