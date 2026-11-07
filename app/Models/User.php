<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_OPERATOR = 'operator';

    public const ROLE_VIEWER = 'viewer';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_OPERATOR => 'Operator',
            self::ROLE_VIEWER => 'Viewer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->is_active && in_array($this->role, [self::ROLE_ADMIN, self::ROLE_OPERATOR], true);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::roles()[$this->role] ?? ucfirst($this->role);
    }

    public function getInitialsAttribute(): string
    {
        return Str::of($this->name)
            ->squish()
            ->explode(' ')
            ->take(2)
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Buat token API baru. Nilai token asli hanya dikembalikan sekali.
     */
    public function regenerateApiToken(): string
    {
        $plainTextToken = 'ta_'.Str::random(40);

        $this->forceFill(['api_token' => hash('sha256', $plainTextToken)])->save();

        return $plainTextToken;
    }

    public function defenseSessions(): HasMany
    {
        return $this->hasMany(DefenseSession::class, 'created_by');
    }
}
