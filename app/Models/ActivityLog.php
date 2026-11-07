<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getToneAttribute(): string
    {
        return match (true) {
            str_contains($this->action, 'created') => 'emerald',
            str_contains($this->action, 'updated') => 'amber',
            str_contains($this->action, 'deleted') => 'rose',
            str_contains($this->action, 'login') => 'sky',
            default => 'slate',
        };
    }

    public function getIconAttribute(): string
    {
        return match (true) {
            str_contains($this->action, 'created') => 'plus',
            str_contains($this->action, 'updated') => 'pencil',
            str_contains($this->action, 'deleted') => 'trash',
            str_contains($this->action, 'login') => 'key',
            default => 'clock',
        };
    }
}
