<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\PlayerAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'game_player_id',
        'question_id',
        'answer_id',
        'is_correct',
        'time_taken',
        'points_earned',
        'streak_bonus',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'time_taken' => 'integer',
            'points_earned' => 'integer',
            'streak_bonus' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<\App\Models\GamePlayer, $this>
     */
    public function gamePlayer(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class);
    }

    /**
     * @return BelongsTo<\App\Models\Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return BelongsTo<\App\Models\Answer, $this>
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }
}
