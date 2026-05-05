<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAiMonitorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_ai_monitor_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.ai-monitor'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_ai_monitor_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.ai-monitor'));

        $response->assertForbidden();
    }
}
