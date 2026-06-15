<?php

namespace App\Models;

use Database\Factories\GameTeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameTeam extends Model
{
    /** @use HasFactory<GameTeamFactory> */
    use HasFactory;

    protected $fillable = [
        'game_session_id',
        'name',
        'color',
        'score',
    ];

    /**
     * Fixed palette used when auto-generating teams for a session.
     *
     * @var array<int, array{name: string, color: string}>
     */
    public const PALETTE = [
        ['name' => 'Red', 'color' => 'red'],
        ['name' => 'Blue', 'color' => 'blue'],
        ['name' => 'Green', 'color' => 'green'],
        ['name' => 'Yellow', 'color' => 'yellow'],
        ['name' => 'Purple', 'color' => 'purple'],
        ['name' => 'Pink', 'color' => 'pink'],
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
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
     * @return HasMany<GamePlayer, $this>
     */
    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }
}
