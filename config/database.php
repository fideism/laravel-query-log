<?php

return [

    'debug' => env('DB_DEBUG', true),

    'channel' => env('DB_LOG_CHANNEL', 'single'),

    'log' => env('DB_LOG_FILE', storage_path('logs/sql.log')),

    'days' => env('DB_LOG_DAYS', 7),

    'level' => env('DB_LOG_LEVEL', 'debug'),

    'explain' => env('DB_EXPLAIN', false),

    'request' => env('DB_REQUEST', false)
];

