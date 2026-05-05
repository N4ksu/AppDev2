<?php

namespace Tests\Unit;

use App\Models\BehaviorSample;
use App\Models\LoginLog;
use App\Models\SecurityInsight;
use App\Models\User;
use App\Services\BehaviorStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BehaviorStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_not_calibrated_without_enough_samples(): void
    {
        $user = User::factory()->create([
            'calibrated_at' => null,
        ]);

        $status = app(BehaviorStatusService::class)->getStatus($user);

        $this->assertSame('not_calibrated', $status['calibration_state']);
    }

    public function test_it_returns_recalibration_recommended_for_stale_calibration(): void
    {
        $user = User::factory()->create([
            'calibrated_at' => now()->subDays(31),
            'calibration_status' => 'calibrated',
        ]);

        for ($i = 0; $i < 12; $i++) {
            BehaviorSample::create([
                'user_id' => $user->id,
                'typing_speed' => 2.1,
                'mouse_velocity' => 0.5,
            ]);
        }

        $status = app(BehaviorStatusService::class)->getStatus($user->fresh());

        $this->assertSame('recalibration_recommended', $status['calibration_state']);
    }

    public function test_it_returns_verification_degraded_when_latest_provider_is_not_success(): void
    {
        $user = User::factory()->create([
            'calibrated_at' => now(),
            'calibration_status' => 'calibrated',
        ]);

        for ($i = 0; $i < 12; $i++) {
            BehaviorSample::create([
                'user_id' => $user->id,
                'typing_speed' => 1.9,
                'mouse_velocity' => 0.4,
            ]);
        }

        $log = LoginLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'status' => 'success',
            'action' => 'login',
            'action_taken' => 'allowed',
            'login_method' => 'password',
            'failed_attempts' => 0,
            'risk_score' => 0,
            'risk_level' => 'safe',
        ]);

        SecurityInsight::create([
            'user_id' => $user->id,
            'login_log_id' => $log->id,
            'severity' => 'medium',
            'reason' => 'provider unavailable',
            'recommendation' => 'monitor',
            'model_name' => 'openai',
            'provider_status' => 'degraded',
            'final_action' => 'monitor',
            'local_risk_band' => 'safe',
            'ai_response_json' => ['status' => 'degraded'],
            'decision_metadata' => [],
        ]);

        $status = app(BehaviorStatusService::class)->getStatus($user->fresh());

        $this->assertSame('verification_degraded', $status['calibration_state']);
    }
}
