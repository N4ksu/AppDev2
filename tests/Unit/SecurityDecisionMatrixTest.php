<?php

namespace Tests\Unit;

use App\Services\SecurityDecisionMatrix;
use Tests\TestCase;

class SecurityDecisionMatrixTest extends TestCase
{
    public function test_it_returns_deterministic_actions_for_success_provider_state(): void
    {
        $matrix = app(SecurityDecisionMatrix::class);

        $result = $matrix->decide('suspicious', 'critical', 'success');
        $this->assertSame('deny', $result['action']);

        $result = $matrix->decide('safe', 'low', 'success');
        $this->assertSame('normal', $result['action']);
    }

    public function test_it_falls_back_to_local_only_when_provider_is_degraded(): void
    {
        $matrix = app(SecurityDecisionMatrix::class);

        $result = $matrix->decide('high_risk', 'critical', 'degraded');
        $this->assertSame('alert', $result['action']);
    }
}
