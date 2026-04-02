<?php

namespace App\Observers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachmentObserver
{
    /**
     * Saat record dihapus permanen (forceDelete) — hapus file fisik.
     * Soft delete TIDAK menghapus file karena file masih bisa di-restore.
     */
    public function forceDeleted(Attachment $attachment): void
    {
        $this->deleteFile($attachment->file_path, $attachment->id);
    }

    /**
     * Saat file_path diganti (user edit lampiran → upload file baru) —
     * hapus file LAMA dari storage agar tidak menumpuk.
     */
    public function updating(Attachment $attachment): void
    {
        if (
            $attachment->isDirty('file_path')
            && $attachment->getOriginal('file_path')
        ) {
            $this->deleteFile(
                $attachment->getOriginal('file_path'),
                $attachment->id
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function deleteFile(?string $filePath, int $attachmentId): void
    {
        if (! $filePath) return;

        try {
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        } catch (\Throwable $e) {
            // Jangan throw — file mungkin sudah tidak ada.
            // Log saja agar bisa diaudit jika perlu cleanup manual.
            Log::warning('AttachmentObserver: gagal hapus file', [
                'attachment_id' => $attachmentId,
                'file_path'     => $filePath,
                'error'         => $e->getMessage(),
            ]);
        }
    }
}