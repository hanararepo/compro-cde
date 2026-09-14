<?php

return [
    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA v3 Configuration
    |--------------------------------------------------------------------------
    |
    | Site key is used in the frontend JavaScript to generate tokens.
    | Secret key is used server-side to verify tokens with Google's API.
    |
    */
    'site_key' => env('RECAPTCHA_SITE_KEY', ''),

    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Score Threshold
    |--------------------------------------------------------------------------
    |
    | reCAPTCHA v3 returns a score between 0.0 (bot) and 1.0 (human).
    | Requests with a score below this threshold will be rejected.
    |
    */
    'threshold' => env('RECAPTCHA_THRESHOLD', 0.5),

    /*
    |--------------------------------------------------------------------------
    | Verify URL
    |--------------------------------------------------------------------------
    |
    | The Google reCAPTCHA verification endpoint.
    |
    */
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
];
