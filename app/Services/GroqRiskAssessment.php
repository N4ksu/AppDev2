<?php

namespace App\Services;

use App\Models\LoginLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqRiskAssessment
{
    private ?string $apiKey;
    private string $baseUrl;
    private string $model;

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are a security analyst. You will receive a login event in JSON. Analyze:
- Time vs typical human working hours (9 AM - 11 PM local time)
- Device and location consistency compared to previous logins (note if device is new or location unusual)
- Biometric method and failure history
- Any behavioral anomalies like typing speed (if present) compared to typical human rates (30-80 WPM)
- Overall risk based on these factors

Return ONLY a valid JSON object with no additional text or markdown. The JSON must contain:
{
  "risk_score": integer 0-100,
  "anomaly_flags": [array of strings],
  "explanation": "1-2 sentences human-readable",
  "recommended_action": "allow" or "step-up" or "block"
}
PROMPT;

    private const FALLBACK = [
        'risk_score' => 50,
        'anomaly_flags' => [],
        'explanation' => 'AI assessment unavailable',
        'recommended_action' => 'allow',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->baseUrl = rtrim((string) config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
        $this->model = (string) config('services.groq.model', 'llama-3.3-70b-versatile');
    }

    /**
     * Assess a login event using Groq and return structured risk data.
     */
    public function assessLoginEvent(LoginLog $log): array
    {
        if (!$this->apiKey) {
            Log::warning('GroqRiskAssessment: GROQ_API_KEY is not set. Skipping AI assessment.');
            return self::FALLBACK;
        }

        $eventJson = json_encode($this->buildEventPayload($log));

        return $this->callWithRetry($eventJson, maxAttempts: 2);
    }

    /**
     * Send a chat message with login event context and return Groq's reply.
     */
    public function chat(string $userMessage, string $eventsJson): ?string
    {
        if (!$this->apiKey) {
            Log::warning('GroqRiskAssessment: GROQ_API_KEY is not set. Chat unavailable.');
            return null;
        }

        $systemPrompt = <<<'PROMPT'
You are an AI security assistant for a "Secure Login Monitoring System". 
Your role is twofold:
1. Analyze recent login events and provide security insights.
2. Answer general questions about the system, its features, and how to use them.

## Knowledge Base
The system supports these authentication methods:
- **Passwordless Passkeys (FIDO2/WebAuthn)**: Users can register a passkey (e.g., fingerprint, face, or device PIN) instead of a password. Registration steps:
  1. Navigate to "Account Settings" > "Security" > "Passkeys".
  2. Click "Register New Passkey".
  3. Authenticate using your device’s biometric or PIN when prompted.
  4. The passkey is stored securely on your device and can be used for future logins.
- **FaceID**: Available on iOS devices. Once the user enables it in app settings, they can log in by scanning their face.
- **Fingerprint**: Available on Android and some Windows devices. Similar setup process in settings.
- **Adaptive Risk Scoring**: The system analyzes each login attempt using AI and assigns a risk score (0-100). Scores above 70 are flagged for review.
- **Security Chat**: This chat interface (what you are) allows operators to query login events and get system help.

## Login Event Data
You will receive a JSON array of the last 20 login events, each with these fields:
- user, timestamp, biometric_type, device, ip_address, location_city, risk_score, anomaly_flags, explanation, recommended_action.

## Instructions
- When the user asks a question about the system itself (like "how to register passkeys" or "what does risk score mean"), use the Knowledge Base above to answer concisely.
- When the user asks about specific login events (e.g., "show me high-risk logins" or "who logged in yesterday"), use the provided login event data.
- If the question cannot be answered from the Knowledge Base or the event data, say: "I don't have that information. Please contact the system administrator."
- Keep answers clear, brief, and in a friendly tone.

Format your responses using Markdown for readability:
- Use **bold** for important terms.
- Use bullet points or numbered lists when listing items.
- Use headings (##) for sections when appropriate.
- Keep code or URLs in backticks.
Be concise but well-structured.
PROMPT;

        try {
            $response = Http::withToken($this->apiKey)
                ->acceptJson()
                ->withoutVerifying()
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "Here are the recent login events: " . substr($eventsJson, 0, 15000)],
                        ['role' => 'user', 'content' => "User Query: " . $userMessage],
                    ],
                    'temperature' => 0.5,
                ]);

            if (!$response->successful()) {
                Log::error('GroqRiskAssessment chat: API returned status ' . $response->status() . ' Body: ' . $response->body());
                return null;
            }

            return $response->json('choices.0.message.content');
        } catch (\Exception $e) {
            Log::error('GroqRiskAssessment chat error: ' . $e->getMessage());
            return null;
        }
    }

    private function callWithRetry(string $eventJson, int $maxAttempts = 2): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                $response = Http::withToken($this->apiKey)
                    ->acceptJson()
                    ->withoutVerifying()
                    ->timeout(20)
                    ->post("{$this->baseUrl}/chat/completions", [
                        'model' => $this->model,
                        'messages' => [
                            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                            ['role' => 'user', 'content' => $eventJson],
                        ],
                        'response_format' => ['type' => 'json_object'],
                        'temperature' => 0.2,
                    ]);

                if (!$response->successful()) {
                    Log::warning("GroqRiskAssessment: API returned status {$response->status()} (attempt {$attempt}). Body: " . $response->body());
                    
                    if ($response->status() === 429 && $attempt < $maxAttempts) {
                        Log::info("GroqRiskAssessment: Rate limit hit (429). Sleeping 10s before retry...");
                        sleep(10);
                        continue;
                    }

                    if ($attempt < $maxAttempts) {
                        sleep(2 * $attempt);
                        continue;
                    }
                    return self::FALLBACK;
                }

                return $this->parseResponse($response->json());
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("GroqRiskAssessment: Request failed (attempt {$attempt}): {$e->getMessage()}");
                if ($attempt < $maxAttempts) {
                    sleep(2 * $attempt);
                }
            }
        }

        Log::error('GroqRiskAssessment: All attempts exhausted.', [
            'last_error' => $lastException?->getMessage(),
        ]);

        return self::FALLBACK;
    }

    private function buildEventPayload(LoginLog $log): array
    {
        return [
            'event_id' => $log->id,
            'user' => $log->email ?? 'unknown',
            'user_id' => $log->user_id,
            'timestamp' => $log->created_at?->toIso8601String(),
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'biometric_type' => $log->login_method,
            'status' => $log->status,
            'failed_attempts' => $log->failed_attempts,
            'local_risk_score' => $log->risk_score,
            'local_risk_level' => $log->risk_level,
            'action_taken' => $log->action_taken,
        ];
    }

    private function parseResponse(array $payload): array
    {
        $rawText = $payload['choices'][0]['message']['content'] ?? null;

        if (!is_string($rawText) || trim($rawText) === '') {
            return self::FALLBACK;
        }

        $decoded = json_decode(trim($rawText), true);

        if (!is_array($decoded)) {
            Log::warning('GroqRiskAssessment: Failed to parse JSON response.', ['raw' => $rawText]);
            return self::FALLBACK;
        }

        $riskScore = isset($decoded['risk_score']) ? (int) $decoded['risk_score'] : self::FALLBACK['risk_score'];
        $riskScore = max(0, min(100, $riskScore));

        $anomalyFlags = isset($decoded['anomaly_flags']) && is_array($decoded['anomaly_flags'])
            ? $decoded['anomaly_flags']
            : self::FALLBACK['anomaly_flags'];

        $explanation = trim((string) ($decoded['explanation'] ?? self::FALLBACK['explanation']));
        if ($explanation === '') {
            $explanation = self::FALLBACK['explanation'];
        }

        $action = (string) ($decoded['recommended_action'] ?? self::FALLBACK['recommended_action']);
        if (!in_array($action, ['allow', 'step-up', 'block'], true)) {
            $action = self::FALLBACK['recommended_action'];
        }

        return [
            'risk_score' => $riskScore,
            'anomaly_flags' => $anomalyFlags,
            'explanation' => $explanation,
            'recommended_action' => $action,
        ];
    }
}
