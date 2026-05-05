<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Get a setting value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );

        Cache::forget("setting.{$key}");
    }
}
