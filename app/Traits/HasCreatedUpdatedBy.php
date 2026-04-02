<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Auto-fill created_by dan updated_by dari user yang sedang login.
 * Gunakan trait ini di semua model yang punya kolom created_by / updated_by.
 */
trait HasCreatedUpdatedBy
{
    public static function bootHasCreatedUpdatedBy(): void
    {
        static::creating(function (self $model): void {
            if (auth()->check()) {
                $model->created_by ??= auth()->id();
                $model->updated_by ??= auth()->id();
            }
        });

        static::updating(function (self $model): void {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
