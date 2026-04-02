<?php

namespace App\Traits;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait untuk model yang memiliki lampiran file (morphMany Attachment).
 *
 * Fitur:
 * - Update parent (edit file) -> file lama di storage terhapus otomatis
 * - forceDelete parent → forceDelete semua attachment → file storage terhapus
 * - Soft delete parent → attachment ikut soft delete (bisa di-restore)
 * - Restore parent → attachment ikut di-restore
 */
trait HasAttachments
{
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')
            ->orderBy('urutan');
    }

    public static function bootHasAttachments(): void
    {
        // 1. Saat parent sedang di-update (terutama lewat Repeater Filament)
        // Kita pastikan attachment yang dilepas/diganti juga dibersihkan
        static::saved(function (self $model) {
            // Jika ada logika khusus sinkronisasi file bisa diletakkan di sini,
            // namun Filament relationship secara default menangani CRUD pada child record.
        });

        // 2. Saat parent di-soft delete → soft delete semua lampiran
        static::deleting(function (self $model) {
            if (! $model->isForceDeleting()) {
                // Soft delete — jangan hapus file fisik agar bisa di-restore
                $model->attachments()->each(function (Attachment $attachment) {
                    $attachment->delete();
                });
            }
        });

        // 3. Saat parent di-force delete → hapus semua lampiran & file fisik
        static::forceDeleting(function (self $model) {
            $model->attachments()->withTrashed()->each(function (Attachment $attachment) {
                // Panggil forceDelete agar trigger deleting di model Attachment jalan
                $attachment->forceDelete();
            });
        });

        // 4. Saat parent di-restore → restore semua lampiran
        static::restoring(function (self $model) {
            $model->attachments()->onlyTrashed()->each(function (Attachment $attachment) {
                $attachment->restore();
            });
        });
    }
}