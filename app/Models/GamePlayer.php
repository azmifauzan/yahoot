<?php

namespace App\Models;

use Database\Factories\GamePlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GamePlayer extends Model
{
    /** @use HasFactory<GamePlayerFactory> */
    use HasFactory;

    protected $fillable = [
        'game_session_id',
        'user_id',
        'nickname',
        'avatar',
        'score',
        'streak',
        'is_connected',
        'player_token',
        'finished_at',
    ];

    protected $hidden = [
        'player_token',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'streak' => 'integer',
            'is_connected' => 'boolean',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<GameSession, $this>
     */
    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<PlayerAnswer, $this>
     */
    public function playerAnswers(): HasMany
    {
        return $this->hasMany(PlayerAnswer::class);
    }
}
