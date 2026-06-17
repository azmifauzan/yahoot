<?php

namespace App\Models;

use App\Enums\PointType;
use App\Enums\QuestionType;
use Database\Factories\BankQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankQuestion extends Model
{
    /** @use HasFactory<BankQuestionFactory> */
    use HasFactory;

    protected $table = 'question_bank_items';

    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'question_text',
        'image',
        'time_limit',
        'points',
        'answers',
    ];

    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'points' => PointType::class,
            'time_limit' => 'integer',
            'answers' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
