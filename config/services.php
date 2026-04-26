<?php

return [
    'phone_verification' => [
        'driver' => env('PHONE_VERIFICATION_DRIVER', 'log'),
        'code_expires_in_minutes' => (int) env('PHONE_VERIFICATION_EXPIRES_MINUTES', 10),
        'resend_cooldown_seconds' => (int) env('PHONE_VERIFICATION_RESEND_COOLDOWN', 60),
        'timeout_seconds' => (int) env('PHONE_VERIFICATION_TIMEOUT_SECONDS', 10),
        'log_channel' => env('PHONE_VERIFICATION_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),
        'expose_code_in_logs' => (bool) env('PHONE_VERIFICATION_EXPOSE_CODE_IN_LOGS', false),
        'brevo' => [
            'api_key' => env('BREVO_SMS_API_KEY'),
            'sender' => env('BREVO_SMS_SENDER', 'MAKASOUK'),
            'endpoint' => env('BREVO_SMS_ENDPOINT', 'https://api.brevo.com/v3/transactionalSMS/sms'),
        ],
        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from_number' => env('TWILIO_FROM_NUMBER'),
            'messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID'),
        ],
        'messagebird' => [
            'api_key' => env('MESSAGEBIRD_API_KEY'),
            'originator' => env('MESSAGEBIRD_ORIGINATOR', 'MAKASOUK'),
            'endpoint' => env('MESSAGEBIRD_ENDPOINT', 'https://rest.messagebird.com/messages'),
        ],
    ],
];
