<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindak_lanjuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recommendation_id')
                  ->constrained('recommendations')->cascadeOnDelete();
 
            // ── Jenis penyelesaian ────────────────────────────────────────
            $table->string('jenis_penyelesaian', 100)->nullable()->index()
                  ->comment('Setor Kas | Pengembalian Barang | Perbaikan Administrasi | Cicilan');
 
            // ── Cicilan plan ──────────────────────────────────────────────
            $table->boolean('is_cicilan')->default(false)->index();
            $table->unsignedTinyInteger('jumlah_cicilan_rencana')->nullable()
                  ->comment('Rencana berapa kali cicil, e.g. 3, 6, 12');

            
            $table->date('tanggal_mulai_cicilan')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable()->index();
            $table->decimal('nilai_per_cicilan_rencana', 18, 2)->nullable();
            $table->decimal('nilai_tindak_lanjut', 18, 2)
                  ->default(0)
                  ->comment('Total nilai tindak lanjut, e.g. total setor kas atau total nilai barang yang dikembalikan');
            // ── Cached progress (updated by CicilanObserver) ──────────────
            $table->unsignedTinyInteger('jumlah_cicilan_realisasi')->default(0);
            $table->decimal('total_terbayar', 18, 2)->default(0);
            $table->decimal('sisa_belum_bayar', 18, 2)->default(0);
 
            // ── Notes ─────────────────────────────────────────────────────
            $table->text('catatan_tl')->nullable()
                  ->comment('e.g. BA Desk 19 Desember 2023');
            $table->text('hambatan')->nullable();
 
            // ── Verification ──────────────────────────────────────────────
            $table->string('status_verifikasi', 40)->default('menunggu_verifikasi')
                  ->comment('menunggu_verifikasi | berjalan | lunas | ditolak');
            $table->foreignId('diverifikasi_oleh')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->text('catatan_verifikasi')->nullable();
 
            // ── Audit trail ───────────────────────────────────────────────
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
 
            // Covers: WHERE recommendation_id = ? AND status_verifikasi = ? AND deleted_at IS NULL
            $table->index(
                ['recommendation_id', 'status_verifikasi', 'deleted_at'],
                'tl_rekom_status_deleted_idx'
            );
            // Dashboard: overdue cicilan query
            $table->index(
                ['is_cicilan', 'tanggal_jatuh_tempo'],
                'tl_cicilan_jatuh_tempo_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjuts');
    }
};

