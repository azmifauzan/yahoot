<?php

namespace App\Models;

use App\Enums\PointType;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'type',
        'question_text',
        'image',
        'time_limit',
        'points',
        'order',
    ];

    /**
     * @var list<string>
     */
    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'points' => PointType::class,
            'time_limit' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->image || ! str_contains($this->image, '/')) {
                return null;
            }

            return Storage::disk(config('quiz.image_disk'))->url($this->image);
        });
    }

    /**
     * @return BelongsTo<\App\Models\Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return HasMany<\App\Models\Answer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('order');
    }
}
