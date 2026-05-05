<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SecurityLogContext
{
    public static function warning(string $message, ?Request $request = null, array $context = []): void
    {
        Log::warning($message, self::buildContext($request, $context));
    }

    public static function error(string $message, ?Request $request = null, array $context = []): void
    {
        Log::error($message, self::buildContext($request, $context));
    }

    public static function exception(string $message, Throwable $exception, ?Request $request = null, array $context = []): void
    {
        Log::error($message, self::buildContext($request, array_merge($context, [
            'exception' => $exception->getMessage(),
        ])));
    }

    private static function buildContext(?Request $request, array $context = []): array
    {
        if (!$request) {
            return $context;
        }

        return array_merge([
            'request_id' => $request->header('X-Request-Id') ?? $request->attributes->get('request_id'),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'endpoint' => $request->path(),
        ], $context);
    }
}
