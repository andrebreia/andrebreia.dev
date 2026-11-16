<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    'release' => env('SENTRY_RELEASE'),

    'environment' => env('SENTRY_ENVIRONMENT'),

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', env('APP_ENV', 'production') === 'local' ? 1.0 : 0.1),

    'enable_logs' => true,

    'logs_channel_level' => env('SENTRY_LOG_LEVEL', 'info'),

    'send_default_pii' => false,

    'max_request_body_size' => 'never',
];
