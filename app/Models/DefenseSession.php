<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DefenseSession extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'dijadwalkan';

    public const STATUS_ONGOING = 'berlangsung';

    public const STATUS_COMPLETED = 'selesai';

    public const STATUS_CANCELLED = 'batal';

    protected $fillable = [
        'room_id',
        'created_by',
        'title',
        'student_name',
        'student_nim',
        'type',
        'date',
        'start_time',
        'end_time',
        'status',
        'supervisor_name',
        'examiner_name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            'proposal' => 'Seminar Proposal',
            'hasil' => 'Seminar Hasil',
            'skripsi' => 'Sidang Skripsi',
            'ujian_lain' => 'Ujian Lainnya',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Dijadwalkan',
            self::STATUS_ONGOING => 'Berlangsung',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Batal',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), function (Builder $query) use ($term) {
            $query->where(function (Builder $query) use ($term) {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('student_name', 'like', "%{$term}%")
                    ->orWhere('student_nim', 'like', "%{$term}%")
                    ->orWhereHas('room', fn (Builder $room) => $room->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%"));
            });
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->search($filters['q'] ?? null)
            ->when(filled($filters['room_id'] ?? null), fn (Builder $query) => $query->where('room_id', $filters['room_id']))
            ->when(filled($filters['building_id'] ?? null), fn (Builder $query) => $query->whereHas(
                'room',
                fn (Builder $room) => $room->where('building_id', $filters['building_id'])
            ))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(filled($filters['type'] ?? null), fn (Builder $query) => $query->where('type', $filters['type']))
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('date', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('date', '<=', $filters['date_to']));
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('date', '>=', today())
            ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_ONGOING])
            ->orderBy('date')
            ->orderBy('start_time');
    }

    /**
     * Cek bentrok jadwal pada ruang dan tanggal yang sama.
     */
    public function scopeConflicting(Builder $query, int $roomId, string $date, string $start, string $end, ?int $ignoreId = null): Builder
    {
        return $query->where('room_id', $roomId)
            ->whereDate('date', $date)
            ->where('status', '!=', self::STATUS_CANCELLED)
            ->where('start_time', '<', self::normalizeTime($end))
            ->where('end_time', '>', self::normalizeTime($start))
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId));
    }

    public static function normalizeTime(?string $time): string
    {
        if (blank($time)) {
            return '00:00:00';
        }

        return strlen($time) === 5 ? $time.':00' : $time;
    }

    public function setStartTimeAttribute(?string $value): void
    {
        $this->attributes['start_time'] = self::normalizeTime($value);
    }

    public function setEndTimeAttribute(?string $value): void
    {
        $this->attributes['end_time'] = self::normalizeTime($value);
    }

    public function getStartTimeLabelAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function getEndTimeLabelAttribute(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    public function getDurationAttribute(): int
    {
        $start = Carbon::createFromFormat('H:i:s', self::normalizeTime($this->start_time));
        $end = Carbon::createFromFormat('H:i:s', self::normalizeTime($this->end_time));

        return max(0, (int) $start->diffInMinutes($end));
    }

    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function getTimeRangeAttribute(): string
    {
        return $this->start_time_label.'–'.$this->end_time_label;
    }

    public function getIsPastAttribute(): bool
    {
        return $this->date->isBefore(today());
    }

    public function getDateLabelAttribute(): string
    {
        return $this->date->translatedFormat('l, d F Y');
    }
}
