<?php

namespace App\Models;

use App\Services\Setting\SettingService;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
    ];

    /** @var array<string, string|null>|null In-memory cache for the current request lifecycle. */
    private static ?array $runtimeCache = null;

    // ─────────────────────────────────────────
    // Static Helpers
    // ─────────────────────────────────────────

    /**
     * Retrieve a setting value by key, with optional default.
     * Results are cached permanently until updated to avoid repeated DB calls.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getAllSettings();

        return $settings[$key] ?? $default;
    }

    /**
     * Retrieve all settings as an associative key => value array (cached).
     *
     * @return array<string, string|null>
     */
    public static function getAllSettings(): array
    {
        if (static::$runtimeCache !== null) {
            return static::$runtimeCache;
        }

        return static::$runtimeCache = Cache::rememberForever('cms_settings', function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear both runtime and persistent cache.
     */
    public static function flushCache(): void
    {
        static::$runtimeCache = null;
        Cache::forget('cms_settings');
    }

    /**
     * Update or insert a setting value and flush the cache.
     */
    public static function set(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => $value]);
        } else {
            $definition = collect((new SettingService)->getSettingDefinitions())
                ->firstWhere('key', $key);

            static::create([
                'key' => $key,
                'value' => $value,
                'group' => $definition['group'] ?? 'general',
                'type' => $definition['type'] ?? 'text',
                'label' => $definition['label'] ?? ucwords(str_replace(['_', '-'], ' ', $key)),
            ]);
        }

        static::flushCache();
    }

    /**
     * Retrieve all settings grouped by their group key.
     *
     * @return array<string, Collection>
     */
    public static function getAllGrouped(): array
    {
        return static::all()->groupBy('group')->toArray();
    }
}
