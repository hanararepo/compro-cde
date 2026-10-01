<?php

return [
    // Use the SMTP connection configured through MAIL_* even if the global mailer is log.
    'mailer' => env('CONTACT_MAILER', 'smtp'),
];
