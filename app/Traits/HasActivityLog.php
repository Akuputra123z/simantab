<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait HasActivityLog
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created'  => 'Membuat ' . class_basename($this),
                'updated'  => 'Mengubah ' . class_basename($this),
                'deleted'  => 'Menghapus ' . class_basename($this),
                'restored' => 'Memulihkan ' . class_basename($this),
                default    => $eventName . ' ' . class_basename($this),
            });
    }

    public function getLogNameToUse(): string
    {
        return strtolower(class_basename($this));
    }
}