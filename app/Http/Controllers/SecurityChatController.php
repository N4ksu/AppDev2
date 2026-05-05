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
            'message' => 'required|string|max:2000',
        ]);

        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $messageText = $request->input('message');

        // 1. Save User Message
        \App\Models\ChatMessage::create([
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $messageText,
        ]);

        // 2. Fetch Context (Last 6 messages) for AI
        $context = \App\Models\ChatMessage::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get()
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        // 3. Fetch Login Events (Role-Scoped)
        $isAdmin = $user->role === 'admin';
        $query = LoginLog::with('user:id,name,email');
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }
        
        $events = $query->latest()->take(20)->get()
            ->map(fn (LoginLog $log) => [
                'user' => $log->user?->name ?? $log->email ?? 'unknown',
                'timestamp' => $log->created_at?->toIso8601String(),
                'biometric_type' => $log->login_method,
                'device' => $log->user_agent,
                'status' => $log->status,
                'risk_score' => $log->ai_risk_score ?? $log->risk_score,
            ]);

        $eventsJson = json_encode($events);

        // 4. Get AI Reply
        $reply = $groq->chat($messageText, $eventsJson, $user->role);

        if ($reply) {
            // 5. Save Assistant Reply
            \App\Models\ChatMessage::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'content' => $reply,
            ]);
        }

        return response()->json([
            'reply' => $reply ?? "AI assistant is currently unavailable.",
        ]);
    }

    public function history(): JsonResponse
    {
        $messages = \App\Models\ChatMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get(['role', 'content']);

        return response()->json(['messages' => $messages]);
    }

    public function clear(): JsonResponse
    {
        \App\Models\ChatMessage::where('user_id', auth()->id())->delete();
        return response()->json(['status' => 'success']);
    }
}
