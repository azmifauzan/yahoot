<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'locale',
        'is_admin',
        'total_xp',
        'games_played',
        'games_won',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'has_password',
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
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'total_xp' => 'integer',
            'games_played' => 'integer',
            'games_won' => 'integer',
        ];
    }

    /**
     * Whether the user has a password set (false for Google-only accounts).
     */
    protected function hasPassword(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->password),
        );
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * @return HasMany<GameSession, $this>
     */
    public function hostedGameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'host_id');
    }

    /**
     * @return HasMany<GamePlayer, $this>
     */
    public function gamePlayers(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    /**
     * @return HasMany<UserBadge, $this>
     */
    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * @return HasOne<LlmSetting, $this>
     */
    public function llmSetting(): HasOne
    {
        return $this->hasOne(LlmSetting::class);
    }

    /**
     * @return HasMany<BankQuestion, $this>
     */
    public function bankQuestions(): HasMany
    {
        return $this->hasMany(BankQuestion::class);
    }

    /**
     * @return BelongsToMany<Quiz, $this>
     */
    public function favoriteQuizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'quiz_favorites')->withTimestamps();
    }
}
