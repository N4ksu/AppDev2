<?php

namespace Tests\Unit;

use App\Services\AiRiskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiRiskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_maps_a_successful_gemini_response(): void
    {
        config()->set('services.gemini.api_key', 'test-key');

        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '{"score":72,"status":"success","reason":"New IP and unusual hour"}'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = app(AiRiskService::class)->assessLogin('user@example.com', '127.0.0.1', 'TestAgent');

        $this->assertSame(72, $result['score']);
        $this->assertSame('success', $result['status']);
        $this->assertSame('New IP and unusual hour', $result['reason']);
    }

    public function test_it_returns_degraded_on_timeout_or_exception(): void
    {
        config()->set('services.gemini.api_key', 'test-key');

        Http::fake(function () {
            throw new \RuntimeException('timeout');
        });

        $result = app(AiRiskService::class)->assessLogin('user@example.com', '127.0.0.1', 'TestAgent');

        $this->assertSame(0, $result['score']);
        $this->assertSame('degraded', $result['status']);
    }

    public function test_it_returns_degraded_when_api_key_is_missing(): void
    {
        config()->set('services.gemini.api_key', null);

        $result = app(AiRiskService::class)->assessLogin('user@example.com', '127.0.0.1', 'TestAgent');

        $this->assertSame(0, $result['score']);
        $this->assertSame('degraded', $result['status']);
    }
}
