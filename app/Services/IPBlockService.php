<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class IPBlockService
{
    protected const CACHE_PREFIX = 'ip_block:';
    protected const DEFAULT_DURATION = 15; // minutes

    /**
     * Temporarily block an IP address.
     */
    public function block(string $ip, int $minutes = null): void
    {
        $duration = $minutes ?? self::DEFAULT_DURATION;
        Cache::put(self::CACHE_PREFIX . $ip, true, now()->addMinutes($duration));
    }

    /**
     * Check if an IP address is currently blocked.
     */
    public function isBlocked(string $ip): bool
    {
        return Cache::has(self::CACHE_PREFIX . $ip);
    }

    /**
     * Unblock an IP address.
     */
    public function unblock(string $ip): void
    {
        Cache::forget(self::CACHE_PREFIX . $ip);
    }
}
