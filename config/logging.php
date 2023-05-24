<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

    /*if ( env('APP_ENV') == 'local') {
       $daily_log_path = storage_path('logs/laravel.log');
    } else {
       $daily_log_path = '/mnt/nfs/logs/homecreditclaims.co.uk/lumen.log';
    }*/
    
    $daily_log_path = storage_path('logs/laravel.log');

      /* $url =  env('ELASTIC_HOST','http://elk.spicy-tees.in:9200');
       $url =  substr($url, 0, strrpos($url, ":", 0));
       $url =   preg_replace('#^http?://#', '', rtrim($url,'/'));
       $port = parse_url( env('ELASTIC_HOST','http://elk.spicy-tees.in:9200'), PHP_URL_PORT);*/

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

    //'default' => env('LOG_CHANNEL', 'stack'),
    'default' => 'stack',

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
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['stdout','daily'],
            'ignore_exceptions' => false,
        ],
        'single' => [
            'driver' => 'single',
            'path' => $daily_log_path,
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => $daily_log_path,
            'level' => 'debug',
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],
        'stdout' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stdout',
            ],
            'level' => 'info',
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],
        'sentry' => [
            'driver' => 'sentry',
            'level'  => 'info',
            'bubble' => true,
        ],
    ],

];
