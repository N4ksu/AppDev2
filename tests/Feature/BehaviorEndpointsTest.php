<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BehaviorEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_behavior_verify_requires_authentication(): void
    {
        $response = $this->postJson('/api/behavior/verify', []);

        $response->assertStatus(401);
    }

    public function test_behavior_verify_returns_envelope_and_risk_score_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/behavior/verify', [
            'avg_mouse_speed' => 1.1,
            'avg_mouse_acceleration' => 0.1,
            'avg_dwell_time' => 130,
            'avg_flight_time' => 90,
            'mouse_event_count' => 20,
            'key_event_count' => 40,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['status', 'code', 'message', 'data' => ['risk_score']]);
    }

    public function test_calibration_endpoint_validates_samples(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('behavior.calibrate'), [
            'samples' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED');
    }

    public function test_behavior_status_returns_calibration_state(): void
    {
        $user = User::factory()->create([
            'calibration_status' => 'calibrated',
            'behavior_sample_count' => 12,
            'calibrated_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson(route('behavior.status'));

        $response->assertOk()
            ->assertJsonPath('code', 'BEHAVIOR_STATUS_READY')
            ->assertJsonStructure(['status', 'code', 'message', 'data' => ['calibration_state', 'sample_count', 'verification_status']]);
    }
}
