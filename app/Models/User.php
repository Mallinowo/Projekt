<?php

namespace App\Models;
use App\Models\UserMatch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const SUBCULTURE_VALUES = ['emo', 'scene', 'goth', 'punk', 'metalhead'];
    public const GENDER_VALUES = ['male', 'female', 'nonbinary', 'other'];
    public const ORIENTATION_VALUES = ['hetero', 'homo', 'bi', 'other'];

    protected $fillable = [
        'name', 'email', 'password', 'age', 'city',
        'preferred_age_min', 'preferred_age_max', 'preferred_max_distance_km',
        'preferred_subcultures', 'city_lat', 'city_lng',
        'gender', 'orientation', 'subculture', 'bio', 'avatar', 'locale', 'onboarding_completed',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'onboarding_completed' => 'boolean',
        'preferred_subcultures' => 'array',
        'city_lat' => 'float',
        'city_lng' => 'float',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('order');
    }

    public function interests(): HasMany
    {
        return $this->hasMany(Interest::class);
    }

    public function artists(): HasMany
    {
        return $this->hasMany(Artist::class);
    }

    public function swipesMade(): HasMany
    {
        return $this->hasMany(Swipe::class, 'swiper_id');
    }

    public function swipesReceived(): HasMany
    {
        return $this->hasMany(Swipe::class, 'swiped_id');
    }

    public function matches(): HasMany
    {
        return UserMatch::where('user1_id', $this->id)->orWhere('user2_id', $this->id);
    }

    public function hasLiked(int $userId): bool
    {
        return $this->swipesMade()
            ->where('swiped_id', $userId)
            ->whereIn('direction', ['like', 'superlike'])
            ->exists();
    }

    public function hasSwiped(int $userId): bool
    {
        return $this->swipesMade()->where('swiped_id', $userId)->exists();
    }

    public function getMatchWith(int $userId): ?UserMatch
    {
        return UserMatch::where(function ($q) use ($userId) {
            $q->where('user1_id', $this->id)->where('user2_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            $q->where('user1_id', $userId)->where('user2_id', $this->id);
        })->first();
    }

    public function getMainPhotoUrl(): ?string
    {
        $photo = $this->photos()->first();
        return $photo ? '/storage/' . $photo->path : null;
    }

    public function getAvatarUrl(): string
    {
        if ($this->avatar) return '/storage/' . $this->avatar;
        $photo = $this->photos()->first();
        if ($photo) return '/storage/' . $photo->path;
        return '';
    }

    public function preferredPartnerGenders(): array
    {
        $orientation = (string) ($this->orientation ?? '');
        $gender = (string) ($this->gender ?? '');

        if ($orientation === 'bi' || $orientation === 'other' || $orientation === '') {
            return self::GENDER_VALUES;
        }

        if ($orientation === 'hetero') {
            return match ($gender) {
                'male' => ['female'],
                'female' => ['male'],
                default => ['male', 'female'],
            };
        }

        if ($orientation === 'homo') {
            return match ($gender) {
                'male' => ['male'],
                'female' => ['female'],
                default => ['nonbinary', 'other'],
            };
        }

        return self::GENDER_VALUES;
    }

    public function acceptsUser(User $other): bool
    {
        $otherGender = (string) ($other->gender ?? '');
        if ($otherGender === '') {
            return true;
        }

        return in_array($otherGender, $this->preferredPartnerGenders(), true);
    }

    public function preferredSubculturesOrAll(): array
    {
        $values = $this->preferred_subcultures;
        if (!is_array($values) || empty($values)) {
            return self::SUBCULTURE_VALUES;
        }

        return collect($values)
            ->map(fn($value) => (string) $value)
            ->filter(fn($value) => in_array($value, self::SUBCULTURE_VALUES, true))
            ->unique()
            ->values()
            ->all();
    }

    public function preferredAgeMin(): int
    {
        $value = (int) ($this->preferred_age_min ?? 18);
        return max(18, min(99, $value));
    }

    public function preferredAgeMax(): int
    {
        $value = (int) ($this->preferred_age_max ?? 99);
        $value = max(18, min(99, $value));
        return max($this->preferredAgeMin(), $value);
    }

    public function preferredMaxDistanceKm(): int
    {
        $value = (int) ($this->preferred_max_distance_km ?? 120);
        return max(5, min(500, $value));
    }
}
