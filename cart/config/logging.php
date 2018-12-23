<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */
    'default' => env('LOG_CHANNEL', 'custom'),
    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['custom'],
        ],
        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],
        'custom' => [
            'driver' => 'custom',
            'name' => env('APP_NAME'),
            'level' => env('APP_DEBUG') ? 'debug' : 'info',
            'log_file' => env('LOG_FILE', 'storage/log/lumen.log'),
            'log_format' => env('LOG_FORMAT', 'json'),
            'max_log_files' => env('MAX_LOG_FILES', 0),
            'via' => \NMI\LumenLogger\Logging\MicroserviceLogger::class,
        ],
    ],

];
