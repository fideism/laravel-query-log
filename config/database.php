<?php

return [

    'debug' => env('DB_DEBUG', true),

    'channel' => env('DB_LOG_CHANNEL', 'single'),

    'log' => env('DB_LOG_FILE', 'sql.log'),

    'days' => env('DB_LOG_DAYS', 7),

    'level' => env('DB_LOG_LEVEL', 'debug'),
];
