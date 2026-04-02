<?php

namespace App\Helpers;

class AuditHelper
{

    
    /**
     * Hitung sisa pembayaran
     */
    public static function sisa(float $target, float $realisasi): float
    {
        return max($target - $realisasi, 0);
    }

    /**
     * Hitung progress %
     */
    public static function progress(float $realisasi, float $target): float
    {
        if ($target == 0) {
            return 0;
        }

        return round(($realisasi / $target) * 100, 2);
    }

    /**
     * Status tindak lanjut
     */
    public static function statusTL(float $target, float $realisasi): string
    {
        if ($realisasi <= 0) {
            return 'menunggu_verifikasi';
        }

        if ($realisasi < $target) {
            return 'berjalan';
        }

        return 'lunas';
    }

    /**
     * Status rekomendasi
     */
    public static function statusRekom(float $target, float $realisasi): string
    {
        if ($realisasi >= $target) {
            return 'selesai';
        }

        if ($realisasi > 0) {
            return 'dalam_proses';
        }

        return 'belum_ditindaklanjuti';
    }
}