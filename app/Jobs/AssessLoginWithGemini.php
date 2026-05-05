<?php

namespace App\Jobs;

use App\Models\LoginLog;
use App\Services\GeminiRiskAssessment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AssessLoginWithGemini implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        private int $loginLogId
    ) {}

    public function handle(\App\Services\GroqRiskAssessment $assessment): void
    {
        $log = LoginLog::find($this->loginLogId);

        if (!$log) {
            Log::info("AssessLoginWithGemini: LoginLog #{$this->loginLogId} no longer exists. Skipping.");
            return;
        }

        $result = $assessment->assessLoginEvent($log);

        $log->update([
            'ai_risk_score' => $result['risk_score'],
            'anomaly_flags' => $result['anomaly_flags'],
            'explanation' => $result['explanation'],
            'recommended_action' => $result['recommended_action'],
        ]);

        Log::info("AssessLoginWithGemini: LoginLog #{$this->loginLogId} assessed.", [
            'ai_risk_score' => $result['risk_score'],
            'recommended_action' => $result['recommended_action'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AssessLoginWithGemini: Job failed for LoginLog #{$this->loginLogId}.", [
            'error' => $exception->getMessage(),
        ]);
    }
}
