<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function all(array $keys = []): array
    {
        $query = static::query();
        if ($keys) {
            $query->whereIn('key', $keys);
        }

        return $query->pluck('value', 'key')->toArray();
    }
}
