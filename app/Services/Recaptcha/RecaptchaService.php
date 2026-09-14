<?php

namespace App\Services\Recaptcha;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $verifyUrl,
        private readonly float $threshold,
    ) {}

    /**
     * Verify a reCAPTCHA v3 token against Google's API.
     *
     * Returns true when the token is valid and the score meets the threshold.
     */
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        try {
            $payload = [
                'secret' => $this->secretKey,
                'response' => $token,
            ];

            if ($remoteIp !== null) {
                $payload['remoteip'] = $remoteIp;
            }

            $response = Http::asForm()
                ->timeout(5)
                ->post($this->verifyUrl, $payload);

            if (! $response->successful()) {
                Log::warning('reCAPTCHA verification HTTP error', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            /** @var array{success: bool, score?: float, action?: string, 'error-codes'?: list<string>} $body */
            $body = $response->json();

            if (! ($body['success'] ?? false)) {
                Log::warning('reCAPTCHA verification failed', [
                    'error_codes' => $body['error-codes'] ?? [],
                ]);

                return false;
            }

            // If score is present (reCAPTCHA v3), verify against threshold.
            // If score is absent (reCAPTCHA v2 Checkbox), success:true is sufficient.
            if (isset($body['score'])) {
                $score = (float) $body['score'];
                return $score >= $this->threshold;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification exception', ['message' => $e->getMessage()]);

            return false;
        }
    }
}
