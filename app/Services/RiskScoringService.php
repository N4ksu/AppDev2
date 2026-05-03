<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RiskScoringService
{
    /**
     * Calculate the risk score for a login attempt.
     * Score range: 0 - 100
     *
     * @param \App\Models\User|null $user
     * @param \Illuminate\Http\Request $request
     * @param string $method (password|passkey)
     * @param bool $success
     * @param string|null $email
     * @return array{score: int, level: string, action: string}
     */
    public function calculateRisk(?User $user, Request $request, string $method, bool $success, ?string $email = null): array
    {
        $score = 0;

        // 1. Unrecognized Identity Check (+25)
        if (!$user) {
            $score += 25;
        }

        // 2. Verified Account Brute Force Check (+40)
        if ($user && $user->failed_attempts >= 3) {
            $score += 40;
        }

        // 3. Global IP Brute Force Check (+45)
        $ipFailures = \App\Models\LoginLog::where('ip_address', $request->ip())
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();
        
        if ($ipFailures >= 5) {
            $score += 45;
        } elseif ($ipFailures >= 3) {
            $score += 20;
        }

        // 4. Target Identity Brute Force Check (+30)
        if ($email) {
            $targetFailures = \App\Models\LoginLog::where('email', $email)
                ->where('status', 'failed')
                ->where('created_at', '>=', now()->subMinutes(15))
                ->count();
            
            if ($targetFailures >= 3) {
                $score += 30;
            }
        }

        // 5. New IP Check (+30)
        if ($user && $user->last_login_ip && $user->last_login_ip !== $request->ip()) {
            $score += 30;
        }

        // 6. Unusual Hour Check (+10)
        $hour = Carbon::now()->hour;
        if ($hour < 6 || $hour > 23) {
            $score += 10;
        }

        // 7. Method Check
        if ($method === 'password') {
            $score += 10;
        } elseif ($method === 'passkey') {
            $score -= 20;
        }

        // 8. Event Failure Increment (+20)
        if (!$success) {
            $score += 20;
        }

        // Clamp score between 0 and 100
        $score = max(0, min(100, $score));

        // Strict Level Decision Logic
        if ($score >= 70) {
            $level = 'high_risk'; // Red
            $action = 'denied';
        } elseif ($score >= 40) {
            $level = 'suspicious'; // Yellow
            $action = 'flagged'; // Suspicious but monitored
        } else {
            $level = 'safe'; // Green
            $action = 'allowed';
        }

        return [
            'score' => $score,
            'level' => $level,
            'action' => $action,
        ];
    }
}
