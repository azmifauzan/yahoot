<?php

namespace App\Models;

use App\Enums\GameStatus;
use Database\Factories\GameSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    /** @use HasFactory<GameSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'host_id',
        'game_code',
        'status',
        'current_question_index',
        'question_started_at',
        'settings',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
            'current_question_index' => 'integer',
            'question_started_at' => 'datetime',
            'settings' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * @return HasMany<GamePlayer, $this>
     */
    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    /**
     * @return HasMany<GameTeam, $this>
     */
    public function teams(): HasMany
    {
        return $this->hasMany(GameTeam::class);
    }
}
