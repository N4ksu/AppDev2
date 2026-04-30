<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BehaviorController extends Controller
{
    protected $riskScorer;

    public function __construct(\App\Services\BehaviorRiskScorer $riskScorer)
    {
        $this->riskScorer = $riskScorer;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'avg_mouse_speed' => 'numeric',
            'avg_mouse_acceleration' => 'numeric',
            'avg_dwell_time' => 'numeric',
            'avg_flight_time' => 'numeric',
            'mouse_event_count' => 'numeric',
            'key_event_count' => 'numeric',
        ]);

        $user = $request->user();

        if ($user) {
            $riskScore = $this->riskScorer->calculateAndUpdateRisk($user, $data);
            return response()->json(['status' => 'ok', 'risk_score' => $riskScore]);
        }

        return response()->json(['status' => 'unauthorized'], 401);
    }
}
