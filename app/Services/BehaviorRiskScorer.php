<?php

namespace App\Services;

use App\Models\UserBehaviorProfile;

class BehaviorRiskScorer
{
    /**
     * Update baseline and calculate anomaly score
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $incomingData
     * @return float Anomaly score (0-100)
     */
    public function calculateAndUpdateRisk($user, array $incomingData): float
    {
        $profile = UserBehaviorProfile::firstOrCreate(
            ['user_id' => $user->getAuthIdentifier()],
            ['baseline_data' => []]
        );

        $baseline = $profile->baseline_data ?? [];

        // Required keys from the frontend sensor
        $keys = [
            'avg_mouse_speed',
            'avg_mouse_acceleration',
            'avg_dwell_time',
            'avg_flight_time'
        ];

        $anomalyScores = [];

        foreach ($keys as $key) {
            if (!isset($incomingData[$key])) {
                continue;
            }

            $val = $incomingData[$key];

            if (!isset($baseline[$key])) {
                $baseline[$key] = [
                    'count' => 0,
                    'mean' => 0,
                    'm2' => 0, // Welford's online algorithm for variance
                ];
            }

            $stats = $baseline[$key];

            // If we have enough data (e.g. 10 samples) calculate z-score
            if ($stats['count'] > 10 && $stats['m2'] > 0) {
                $variance = $stats['m2'] / $stats['count'];
                $stdDev = sqrt($variance);
                
                // Z-score formula: |(X - μ) / σ|
                if ($stdDev > 0) {
                    $zScore = abs(($val - $stats['mean']) / $stdDev);
                    // Cap Z-score at say, 5 (which is extremely anomalous)
                    $zScore = min($zScore, 5);
                    // Normalize to 0-100
                    $anomalyScores[] = ($zScore / 5) * 100;
                }
            }

            // Update running mean and variance using Welford's
            $stats['count']++;
            $delta = $val - $stats['mean'];
            $stats['mean'] += $delta / $stats['count'];
            $delta2 = $val - $stats['mean'];
            $stats['m2'] += $delta * $delta2;

            $baseline[$key] = $stats;
        }

        $profile->baseline_data = $baseline;
        $profile->save();

        if (empty($anomalyScores)) {
            // Not enough baseline data or missing keys
            return 0;
        }

        // Return average anomaly score across metrics
        $currentRisk = array_sum($anomalyScores) / count($anomalyScores);
        
        // Exponential moving average for session risk to prevent sudden spikes
        $previousRisk = session('session_risk_score', 0);
        $alpha = 0.2; // How much weight to give the new reading
        
        $newSessionRisk = ($alpha * $currentRisk) + ((1 - $alpha) * $previousRisk);
        
        session(['session_risk_score' => $newSessionRisk]);
        
        return $newSessionRisk;
    }
}
