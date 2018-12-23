<?php

use GreenSmoke\HealthChecks\Checks\Environment\CorrectEnvironment;
use GreenSmoke\HealthChecks\Checks\Environment\DebugModeOff;
use GreenSmoke\HealthChecks\Checks\Filesystem\PathIsWritable;
use GreenSmoke\HealthChecks\Checks\Database\MongoDBOnline;
use GreenSmoke\HealthChecks\Checks\Upstream\UpstreamOnline;
use GreenSmoke\HealthChecks\Checks\Filesystem\LogFileIsWritable;

return [
    'checks' => [
        new DebugModeOff(),
        new CorrectEnvironment('production'),
        new LogFileIsWritable(env('LOG_FILE')),
        new PathIsWritable(storage_path('framework/cache')),
        new MongoDBOnline(),
        new UpstreamOnline(env('CUSTOMERS_URL')),
        new UpstreamOnline(env('TAXES_URL'))
    ],
    'route'  => [
        'enabled' => true,
    ]
];
