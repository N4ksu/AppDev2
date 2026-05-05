<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TypingDNAService;

class TypingDNAMiddleware
{
    private $typingDNAService;

    public function __construct(TypingDNAService $typingDNAService)
    {
        $this->typingDNAService = $typingDNAService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Check if typing pattern is present
        if ($request->has('typingPattern')) {
            $userId = $request->user() ? $request->user()->id : $request->email;
            $result = $this->typingDNAService->verify($userId, $request->typingPattern);

            // If verification fails, deny access
            if (isset($result['result']) && $result['result'] === 0) {
                return redirect()->back()->with('status', 'Typing pattern verification failed. Please try again.');
            }

            if (isset($result['status']) && $result['status'] >= 400) {
                \Illuminate\Support\Facades\Log::warning("TypingDNA API returned error: " . ($result['message'] ?? 'Unknown error'));
            }
        }

        return $next($request);
    }
}
