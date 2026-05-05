<?php

namespace App\Services;

use App\Models\SecurityInsight;
use App\Models\User;

class BehaviorStatusService
{
    /**
     * @return array{
     *   calibration_state: string,
     *   calibrated_at: ?string,
     *   sample_count: int,
     *   last_verification_at: ?string,
     *   verification_status: string
     * }
     */
    public function getStatus(User $user): array
    {
        $sampleCount = $user->behaviorSamples()->count();
        $latestInsight = $user->securityInsights()->latest()->first();
        $verificationStatus = $latestInsight && $latestInsight->provider_status !== 'success'
            ? 'degraded'
            : 'normal';

        $state = $this->resolveCalibrationState($user, $sampleCount, $verificationStatus);

        return [
            'calibration_state' => $state,
            'calibrated_at' => optional($user->calibrated_at)->toDateTimeString(),
            'sample_count' => $sampleCount,
            'last_verification_at' => optional($user->last_behavior_verification_at)->toDateTimeString(),
            'verification_status' => $verificationStatus,
        ];
    }

    /**
     * @return array{
     *   latest_local_risk_band: string,
     *   latest_ai_severity: string,
     *   provider_status: string,
     *   final_action: string,
     *   updated_at: ?string
     * }
     */
    public function getVerificationSummary(User $user): array
    {
        $latest = SecurityInsight::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$latest) {
            return [
                'latest_local_risk_band' => 'safe',
                'latest_ai_severity' => 'low',
                'provider_status' => 'degraded',
                'final_action' => 'monitor',
                'updated_at' => null,
            ];
        }

        return [
            'latest_local_risk_band' => $latest->local_risk_band,
            'latest_ai_severity' => $latest->severity,
            'provider_status' => $latest->provider_status,
            'final_action' => $latest->final_action,
            'updated_at' => optional($latest->created_at)->toDateTimeString(),
        ];
    }

    public function markCalibrated(User $user, int $sampleCount): void
    {
        $user->update([
            'calibrated_at' => now(),
            'calibration_status' => 'calibrated',
            'behavior_sample_count' => $sampleCount,
        ]);
    }

    public function markVerified(User $user): void
    {
        $user->update([
            'last_behavior_verification_at' => now(),
        ]);
    }

    private function resolveCalibrationState(User $user, int $sampleCount, string $verificationStatus): string
    {
        if ($verificationStatus === 'degraded') {
            return 'verification_degraded';
        }

        if (!$user->calibrated_at || $sampleCount < 10) {
            return 'not_calibrated';
        }

        if ($user->calibrated_at->lt(now()->subDays(30))) {
            return 'recalibration_recommended';
        }

        return 'calibrated';
    }
}
