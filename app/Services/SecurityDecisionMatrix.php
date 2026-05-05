<?php

namespace App\Services;

class SecurityDecisionMatrix
{
    /**
     * @return array{action: string, recommendation: string, severity: string}
     */
    public function decide(string $localRiskBand, string $aiSeverity, string $providerStatus): array
    {
        $providerStatus = in_array($providerStatus, ['success', 'degraded', 'error'], true) ? $providerStatus : 'degraded';
        $localRiskBand = in_array($localRiskBand, ['safe', 'suspicious', 'high_risk'], true) ? $localRiskBand : 'safe';
        $aiSeverity = in_array($aiSeverity, ['low', 'medium', 'high', 'critical'], true) ? $aiSeverity : 'low';

        if ($providerStatus !== 'success') {
            return $this->localOnlyDecision($localRiskBand);
        }

        $matrix = [
            'safe' => [
                'low' => 'normal',
                'medium' => 'monitor',
                'high' => 'step_up_auth',
                'critical' => 'step_up_auth',
            ],
            'suspicious' => [
                'low' => 'monitor',
                'medium' => 'step_up_auth',
                'high' => 'alert',
                'critical' => 'deny',
            ],
            'high_risk' => [
                'low' => 'step_up_auth',
                'medium' => 'alert',
                'high' => 'deny',
                'critical' => 'deny',
            ],
        ];

        $action = $matrix[$localRiskBand][$aiSeverity];

        return [
            'action' => $action,
            'recommendation' => $this->toRecommendation($action),
            'severity' => $aiSeverity,
        ];
    }

    /**
     * @return array{action: string, recommendation: string, severity: string}
     */
    private function localOnlyDecision(string $localRiskBand): array
    {
        return match ($localRiskBand) {
            'high_risk' => ['action' => 'alert', 'recommendation' => 'monitor', 'severity' => 'high'],
            'suspicious' => ['action' => 'monitor', 'recommendation' => 'monitor', 'severity' => 'medium'],
            default => ['action' => 'normal', 'recommendation' => 'none', 'severity' => 'low'],
        };
    }

    private function toRecommendation(string $action): string
    {
        return match ($action) {
            'normal' => 'none',
            'monitor' => 'monitor',
            'step_up_auth' => 'step_up_auth',
            'alert' => 'admin_alert',
            'deny' => 'deny',
            default => 'monitor',
        };
    }
}
