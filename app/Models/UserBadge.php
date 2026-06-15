<?php

namespace App\Models;

use Database\Factories\UserBadgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    /** @use HasFactory<UserBadgeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_key',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
