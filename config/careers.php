<?php

return [
    'mail_to' => env('CAREERS_MAIL_TO'),
    'mailer' => env('CAREERS_MAILER', 'smtp'),
    // Total attempts in the submit request, including the first send.
    'max_attempts' => 3,
];
