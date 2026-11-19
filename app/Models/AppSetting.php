<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Ambil nilai setting berdasarkan key dengan cache
     */
    public static function get(string $key, $default = null): ?string
    {
        try {
            return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
                try {
                    $setting = static::where('key', $key)->first();
                    return $setting ? $setting->value : $default;
                } catch (\Throwable $e) {
                    return $default;
                }
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set atau update nilai setting dan refresh cache
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("app_setting_{$key}");
    }
}
