<?php

namespace App\Rules;

use App\Services\Recaptcha\RecaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRecaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skip verification in the testing environment so feature tests
        // do not need to hit the Google API.
        if (app()->environment('testing')) {
            return;
        }

        if (! is_string($value) || trim($value) === '') {
            $fail(__('The reCAPTCHA verification failed. Please try again.'));

            return;
        }

        $service = new RecaptchaService(
            secretKey: config('recaptcha.secret_key'),
            verifyUrl: config('recaptcha.verify_url'),
            threshold: (float) config('recaptcha.threshold'),
        );

        if (! $service->verify($value, request()->ip())) {
            $fail(__('The reCAPTCHA verification failed. Please try again.'));
        }
    }
}
