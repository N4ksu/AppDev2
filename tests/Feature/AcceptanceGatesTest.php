<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AcceptanceGatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_fortify_route_contracts_are_available(): void
    {
        $this->assertTrue(Route::has('login.store'));
        $this->assertTrue(Route::has('webauthn.login'));
    }

    public function test_behavior_verify_response_matches_envelope_schema(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/behavior/verify', [
            'avg_mouse_speed' => 1,
            'avg_mouse_acceleration' => 1,
            'avg_dwell_time' => 1,
            'avg_flight_time' => 1,
            'mouse_event_count' => 1,
            'key_event_count' => 1,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['status', 'code', 'message', 'data']);
    }
}
