<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Support\ErrorCode;
use App\Support\SecurityLogContext;

class AiRiskService
{
    private ?string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->model = (string) config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Check a login for risk.
     */
    public function assessLogin(string $identityKey, string $ipAddress, string $userAgent, string $outcome = 'success'): array
    {
        /** @var Request|null $request */
        $request = request();

        if (!$this->apiKey) {
            SecurityLogContext::warning('Gemini API key missing, using degraded mode.', $request, [
                'provider' => 'gemini',
                'error_code' => ErrorCode::CONFIG_MISSING,
            ]);
            return ['score' => 0, 'status' => 'degraded', 'reason' => 'Gemini configuration missing'];
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->post("{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [[
                        'parts' => [[
                            'text' => $this->buildPrompt($identityKey, $ipAddress, $userAgent, $outcome),
                        ]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

            if (!$response->successful()) {
                SecurityLogContext::error('Gemini API bad response.', $request, [
                    'provider' => 'gemini',
                    'error_code' => ErrorCode::API_BAD_RESPONSE,
                    'http_status' => $response->status(),
                ]);

                return ['score' => 0, 'status' => 'degraded', 'reason' => 'Gemini unavailable'];
            }

            $parsed = $this->parseGeminiJson($response->json());
            if (!$parsed) {
                SecurityLogContext::warning('Gemini response parse failed.', $request, [
                    'provider' => 'gemini',
                    'error_code' => ErrorCode::API_BAD_RESPONSE,
                ]);
                return ['score' => 0, 'status' => 'degraded', 'reason' => 'Gemini response invalid'];
            }

            return [
                'score' => $parsed['score'],
                'status' => $parsed['status'],
                'reason' => $parsed['reason'],
            ];
        } catch (\Exception $e) {
            SecurityLogContext::exception('Gemini API request failed.', $e, $request, [
                'provider' => 'gemini',
                'error_code' => ErrorCode::API_TIMEOUT,
            ]);

            return [
                'score' => 0,
                'status' => 'degraded',
                'reason' => 'Gemini timeout or network exception',
            ];
        }
    }

    private function buildPrompt(string $identityKey, string $ipAddress, string $userAgent, string $outcome): string
    {
        return <<<PROMPT
You are a login risk evaluator.
Given this authentication context:
- identity_key: {$identityKey}
- ip_address: {$ipAddress}
- user_agent: {$userAgent}
- authentication_outcome: {$outcome}

Return strict JSON with this shape only:
{"score": <int 0-100>, "status": "success", "reason": "<short reason>"}

Rules:
- score 0 is no risk, 100 is highest risk.
- Keep reason concise and security-focused.
- Do not include markdown or extra text.
PROMPT;
    }

    private function parseGeminiJson(array $payload): ?array
    {
        $rawText = $payload['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($rawText) || trim($rawText) === '') {
            return null;
        }

        $clean = preg_replace('/^```json|```$/m', '', trim($rawText));
        $decoded = json_decode((string) $clean, true);
        if (!is_array($decoded)) {
            return null;
        }

        $score = (int) ($decoded['score'] ?? 0);
        $score = max(0, min(100, $score));

        $status = (string) ($decoded['status'] ?? 'success');
        if (!in_array($status, ['success', 'error', 'degraded'], true)) {
            $status = 'success';
        }

        $reason = trim((string) ($decoded['reason'] ?? 'Analyzed by Gemini'));
        if ($reason === '') {
            $reason = 'Analyzed by Gemini';
        }

        return [
            'score' => $score,
            'status' => $status,
            'reason' => $reason,
        ];
    }
}
