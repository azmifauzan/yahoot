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

    /**
     * Fetch multiple settings as a key/value map.
     *
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    public static function getMany(array $keys = []): array
    {
        $query = static::query();
        if ($keys) {
            $query->whereIn('key', $keys);
        }

        return $query->pluck('value', 'key')->toArray();
    }
}
