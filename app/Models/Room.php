<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'code',
        'name',
        'floor',
        'type',
        'capacity',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'capacity' => 'integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            'ruang_sidang' => 'Ruang Sidang',
            'ruang_ujian' => 'Ruang Ujian',
            'laboratorium' => 'Laboratorium',
            'aula' => 'Aula / Seminar',
            'ruang_diskusi' => 'Ruang Diskusi',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            'tersedia' => 'Tersedia',
            'perbaikan' => 'Perbaikan',
            'nonaktif' => 'Nonaktif',
        ];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class)
            ->withPivot(['quantity', 'condition', 'note'])
            ->withTimestamps();
    }

    public function defenseSessions(): HasMany
    {
        return $this->hasMany(DefenseSession::class);
    }

    public function upcomingSessions(): HasMany
    {
        return $this->defenseSessions()
            ->whereDate('date', '>=', today())
            ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
            ->orderBy('date')
            ->orderBy('start_time');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), function (Builder $query) use ($term) {
            $query->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhereHas('building', fn (Builder $building) => $building->where('name', 'like', "%{$term}%"));
            });
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->search($filters['q'] ?? null)
            ->when(filled($filters['building_id'] ?? null), fn (Builder $query) => $query->where('building_id', $filters['building_id']))
            ->when(filled($filters['type'] ?? null), fn (Builder $query) => $query->where('type', $filters['type']))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(filled($filters['facility_id'] ?? null), fn (Builder $query) => $query->whereHas(
                'facilities',
                fn (Builder $facility) => $facility->where('facilities.id', $filters['facility_id'])
            ))
            ->when(filled($filters['min_capacity'] ?? null), fn (Builder $query) => $query->where('capacity', '>=', (int) $filters['min_capacity']));
    }

    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function getFloorLabelAttribute(): string
    {
        return $this->floor === 0 ? 'Lantai Dasar' : 'Lantai '.$this->floor;
    }

    public function getFullNameAttribute(): string
    {
        return $this->code.' — '.$this->name;
    }

    /**
     * Status ruang yang memperhitungkan jadwal yang sedang berlangsung.
     */
    public function getLiveStatusAttribute(): string
    {
        if ($this->status !== 'tersedia') {
            return $this->status;
        }

        $busy = $this->defenseSessions()
            ->whereDate('date', today())
            ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
            ->where('start_time', '<=', now()->format('H:i:s'))
            ->where('end_time', '>', now()->format('H:i:s'))
            ->exists();

        return $busy ? 'digunakan' : 'tersedia';
    }

    /**
     * @return array{baik: int, rusak_ringan: int, rusak_berat: int}
     */
    public function facilityConditions(): array
    {
        $summary = ['baik' => 0, 'rusak_ringan' => 0, 'rusak_berat' => 0];

        foreach ($this->facilities as $facility) {
            $condition = $facility->pivot->condition;
            if (array_key_exists($condition, $summary)) {
                $summary[$condition]++;
            }
        }

        return $summary;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'tersedia';
    }
}
