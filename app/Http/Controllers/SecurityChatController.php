<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Services\GroqRiskAssessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityChatController extends Controller
{
    public function __invoke(Request $request, GroqRiskAssessment $groq): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Fetch last 20 login events with trimmed fields for token efficiency
        $events = LoginLog::with('user:id,name,email')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn (LoginLog $log) => [
                'user' => $log->user?->name ?? $log->email ?? 'unknown',
                'email' => $log->email,
                'timestamp' => $log->created_at?->toIso8601String(),
                'biometric_type' => $log->login_method,
                'device' => $log->user_agent,
                'ip_address' => $log->ip_address,
                'status' => $log->status,
                'risk_score' => $log->ai_risk_score ?? $log->risk_score,
                'anomaly_flags' => $log->anomaly_flags ?? [],
                'explanation' => $log->explanation ?? '',
                'recommended_action' => $log->recommended_action ?? 'allow',
            ]);

        $eventsJson = json_encode($events, JSON_PRETTY_PRINT);

        $reply = $groq->chat($request->input('message'), $eventsJson);

        if ($reply === null) {
            return response()->json([
                'reply' => "I'm sorry, the AI security assistant is currently hit by a rate limit or quota issue (429) from Groq. Please check your GROQ_API_KEY."
            ]);
        }

        return response()->json(['reply' => $reply]);
    }
}
