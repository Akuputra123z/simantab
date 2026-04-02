<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    // Activity log tidak perlu softDeletes — history harus permanen
    public $timestamps = true;

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeLogName($query, string $name)
    {
        return $query->where('log_name', $name);
    }

    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeForSubject($query, Model $subject)
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    public function scopeForCauser($query, Model $causer)
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getOldAttribute(): ?array
    {
        return $this->properties['old'] ?? null;
    }

    public function getNewAttribute(): ?array
    {
        return $this->properties['new'] ?? null;
    }

    /**
     * Catat log dengan format standar.
     */
    public static function record(
        string $logName,
        string $event,
        string $description,
        Model $subject,
        ?Model $causer = null,
        array $properties = []
    ): self {
        return static::create([
            'log_name'     => $logName,
            'event'        => $event,
            'description'  => $description,
            'subject_type' => $subject->getMorphClass(),
            'subject_id'   => $subject->getKey(),
            'causer_type'  => $causer?->getMorphClass(),
            'causer_id'    => $causer?->getKey(),
            'properties'   => $properties,
        ]);
    }

    public function getEventLabelAttribute(): string
{
    return match ($this->event) {
        'created' => 'Dibuat',
        'updated' => 'Diubah',
        'deleted' => 'Dihapus',
        'restored' => 'Dipulihkan',
        default => ucfirst($this->event ?? '-'),
    };
}

public function getEventColorAttribute(): string
{
    return match ($this->event) {
        'created' => 'success',
        'updated' => 'info',
        'deleted' => 'danger',
        'restored' => 'warning',
        default => 'gray',
    };
}

    
}