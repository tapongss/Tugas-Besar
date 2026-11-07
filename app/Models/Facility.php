<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'display' => 'Display & Proyeksi',
            'komputer' => 'Komputer & Perangkat',
            'audio' => 'Audio & Suara',
            'jaringan' => 'Jaringan & Konektivitas',
            'furnitur' => 'Furnitur & Interior',
            'pendingin' => 'Pendingin Ruangan',
            'lainnya' => 'Lainnya',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function conditions(): array
    {
        return [
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
        ];
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class)
            ->withPivot(['quantity', 'condition', 'note'])
            ->withTimestamps();
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), function (Builder $query) use ($term) {
            $query->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->search($filters['q'] ?? null)
            ->when(filled($filters['category'] ?? null), fn (Builder $query) => $query->where('category', $filters['category']))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('is_active', $filters['status'] === 'aktif'));
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categories()[$this->category] ?? ucfirst($this->category);
    }

    public function getConditionLabelAttribute(): string
    {
        return self::conditions()[$this->condition] ?? ucfirst((string) $this->condition);
    }

    public function getIconNameAttribute(): string
    {
        return match ($this->category) {
            'display' => 'projector',
            'komputer' => 'cpu',
            'audio' => 'speaker',
            'jaringan' => 'wifi',
            'furnitur' => 'chair',
            'pendingin' => 'snow',
            default => 'sparkles',
        };
    }
}
