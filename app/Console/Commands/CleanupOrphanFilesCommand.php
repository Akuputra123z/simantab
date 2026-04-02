<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Bersihkan file di storage yang tidak punya record di tabel attachments.
 *
 * Jalankan sekali untuk membersihkan file lama yang sudah menumpuk:
 *   php artisan attachments:cleanup-orphans --dry-run
 *   php artisan attachments:cleanup-orphans
 */
class CleanupOrphanFilesCommand extends Command
{
    protected $signature   = 'attachments:cleanup-orphans
                                {--dry-run : Tampilkan file yang akan dihapus tanpa benar-benar menghapus}
                                {--disk=public : Disk storage yang digunakan}';

    protected $description = 'Hapus file di storage yang tidak punya record di tabel attachments';

    public function handle(): int
    {
        $disk   = $this->option('disk');
        $dryRun = $this->option('dry-run');

        $this->info('');
        $this->info($dryRun ? '[DRY RUN] Scan file orphan...' : 'Membersihkan file orphan...');

        // Ambil semua file_path yang masih punya record (termasuk soft deleted)
        $knownPaths = Attachment::withTrashed()
            ->whereNotNull('file_path')
            ->pluck('file_path')
            ->flip(); // flip agar O(1) lookup

        // Scan direktori audit di storage
        $directories = ['lhp', 'audit'];
        $deleted     = 0;
        $skipped     = 0;
        $totalSize   = 0;

        foreach ($directories as $dir) {
            if (! Storage::disk($disk)->exists($dir)) continue;

            $files = Storage::disk($disk)->allFiles($dir);

            foreach ($files as $file) {
                if ($knownPaths->has($file)) {
                    $skipped++;
                    continue;
                }

                // File tidak ada di DB — orphan
                $size = Storage::disk($disk)->size($file);
                $totalSize += $size;

                if ($dryRun) {
                    $this->line("  [ORPHAN] {$file} (" . $this->formatBytes($size) . ")");
                } else {
                    Storage::disk($disk)->delete($file);
                    $this->line("  [HAPUS]  {$file}");
                }

                $deleted++;
            }
        }

        $this->info('');
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['File dikenali (skip)',   $skipped],
                ['File orphan ' . ($dryRun ? '(akan dihapus)' : '(dihapus)'), $deleted],
                ['Total ukuran',           $this->formatBytes($totalSize)],
            ]
        );

        if ($dryRun && $deleted > 0) {
            $this->warn('Jalankan tanpa --dry-run untuk benar-benar menghapus file.');
        }

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}