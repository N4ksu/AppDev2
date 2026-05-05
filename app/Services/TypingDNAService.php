<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TypingDNAService
{
    private $apiKey;
    private $apiSecret;
    private $baseUrl = 'https://api.typingdna.com';

    public function __construct()
    {
        $this->apiKey = config('services.typingdna.key');
        $this->apiSecret = config('services.typingdna.secret');
    }

    public function enroll($userId, $typingPattern)
    {
        return $this->request('POST', '/auto/' . $userId, [
            'tp' => $typingPattern,
        ]);
    }

    public function verify($userId, $typingPattern)
    {
        return $this->request('POST', '/auto/' . $userId, [
            'tp' => $typingPattern,
            'check' => 1,
        ]);
    }

    private function request($method, $endpoint, $data = [])
    {
        \Illuminate\Support\Facades\Log::info("TypingDNA Request: {$method} {$endpoint}", ['data_len' => strlen($data['tp'] ?? '')]);

        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->withoutVerifying()
            ->asForm()
            ->$method($this->baseUrl . $endpoint, $data);

        if ($response->failed()) {
            \Illuminate\Support\Facades\Log::error("TypingDNA Error: " . $response->body());
        }

        return $response->json();
    }
}
