<?php

namespace App\Models;

use App\Enums\LlmProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LlmSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'base_url',
        'api_key',
    ];

    protected function casts(): array
    {
        return [
            'provider' => LlmProvider::class,
            'api_key' => 'encrypted',
        ];
    }

    /**
     * Whether enough information is present to call the LLM provider.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->provider) && ! empty($this->model) && ! empty($this->api_key);
    }

    /**
     * The base URL to use, falling back to the provider's default.
     */
    public function resolvedBaseUrl(): string
    {
        return $this->base_url ?: $this->provider->defaultBaseUrl();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
