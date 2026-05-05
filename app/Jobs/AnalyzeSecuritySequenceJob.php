<?php

namespace App\Jobs;

use App\Models\LoginLog;
use App\Models\SecurityInsight;
use App\Services\AiRiskService;
use App\Services\SecurityDecisionMatrix;
use App\Support\SecurityLogContext;
use App\Events\SecurityInsightCreated;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class AnalyzeSecuritySequenceJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [30, 120, 300];

    public int $uniqueFor = 600;

    public function __construct(
        public int $loginLogId,
        public int $analysisVersion = 1
    ) {
        $this->onQueue('security-analysis');
    }

    public function uniqueId(): string
    {
        return $this->loginLogId . ':' . $this->analysisVersion;
    }

    public function handle(AiRiskService $aiRiskService, SecurityDecisionMatrix $decisionMatrix): void
    {
        $log = LoginLog::query()->find($this->loginLogId);
        if (!$log) {
            return;
        }

        if (SecurityInsight::where('login_log_id', $log->id)->exists()) {
            return;
        }

        $recentLogs = LoginLog::query()
            ->where(function ($query) use ($log) {
                $query->where('user_id', $log->user_id);
                if (!$log->user_id && $log->email) {
                    $query->orWhere('email', $log->email);
                }
            })
            ->latest()
            ->take(5)
            ->get(['id', 'status', 'risk_score', 'ip_address', 'user_agent', 'created_at']);

        $ai = $aiRiskService->assessLogin(
            identityKey: $log->email ?? ('user:' . ($log->user_id ?? 'unknown')),
            ipAddress: (string) $log->ip_address,
            userAgent: (string) $log->user_agent,
            outcome: $log->status === 'success' ? 'success' : 'failure'
        );

        $localRiskBand = $this->toRiskBand((int) $log->risk_score);
        $aiSeverity = $this->toSeverity((int) ($ai['score'] ?? 0));
        $decision = $decisionMatrix->decide($localRiskBand, $aiSeverity, (string) ($ai['status'] ?? 'degraded'));

        $insight = SecurityInsight::create([
            'user_id' => $log->user_id,
            'login_log_id' => $log->id,
            'severity' => $decision['severity'],
            'reason' => (string) ($ai['reason'] ?? 'AI analysis unavailable'),
            'recommendation' => $decision['recommendation'],
            'model_name' => (string) config('services.gemini.model', 'gemini-2.5-flash'),
            'provider_status' => (string) ($ai['status'] ?? 'degraded'),
            'final_action' => $decision['action'],
            'local_risk_band' => $localRiskBand,
            'ai_response_json' => $ai,
            'decision_metadata' => [
                'recent_logs' => $recentLogs->toArray(),
                'analysis_version' => $this->analysisVersion,
            ],
        ]);

        event(new SecurityInsightCreated($insight));
    }

    public function failed(Throwable $exception): void
    {
        SecurityLogContext::exception('Security insight analysis job failed.', $exception, null, [
            'provider' => 'gemini',
            'job' => self::class,
            'login_log_id' => $this->loginLogId,
        ]);
    }

    private function toRiskBand(int $score): string
    {
        return match (true) {
            $score >= 70 => 'high_risk',
            $score >= 40 => 'suspicious',
            default => 'safe',
        };
    }

    private function toSeverity(int $score): string
    {
        return match (true) {
            $score >= 85 => 'critical',
            $score >= 65 => 'high',
            $score >= 35 => 'medium',
            default => 'low',
        };
    }
}
