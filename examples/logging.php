<?php

declare(strict_types=1);

/*
 * This file is part of the WP Mail Transport package.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 license that is bundled
 * with this source code in the file COPYING.
 */

/**
 * Logging Configuration Example for WP Mail Transport Debug Mode
 *
 * This is an example configuration file showing how to configure Laravel's logging
 * system to capture debug messages from WP Mail Transport.
 *
 * Copy this configuration to your config/logging.php file or merge with existing config.
 */

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    */
    'default' => \env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    */
    'deprecations' => [
        'channel' => \env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => \env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    */
    'channels' => [
        // Stack channel - combines multiple channels
        'stack' => [
            'driver' => 'stack',
            'channels' => \explode(',', \env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        // Single file channel
        'single' => [
            'driver' => 'single',
            'path' => \storage_path('logs/laravel.log'),
            'level' => \env('LOG_LEVEL', 'debug'), // Include debug level for WP Mail Transport
        ],

        // Daily rotating files
        'daily' => [
            'driver' => 'daily',
            'path' => \storage_path('logs/laravel.log'),
            'level' => \env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'replace_placeholders' => true,
        ],

        // Separate channel for mail debugging
        'mail' => [
            'driver' => 'daily',
            'path' => \storage_path('logs/mail.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        // Slack channel for important errors
        'slack' => [
            'driver' => 'slack',
            'url' => \env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => \env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        // Papertrail for cloud logging
        'papertrail' => [
            'driver' => 'monolog',
            'level' => \env('LOG_LEVEL', 'debug'),
            'handler' => \env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => \env('PAPERTRAIL_URL'),
                'port' => \env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.\env('PAPERTRAIL_URL').':'.\env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        // Stderr (useful for Docker/containers)
        'stderr' => [
            'driver' => 'monolog',
            'level' => \env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => \env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        // Syslog
        'syslog' => [
            'driver' => 'syslog',
            'level' => \env('LOG_LEVEL', 'debug'),
            'facility' => \env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        // PHP error log
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => \env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        // Null (disable logging)
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        // Emergency channel
        'emergency' => [
            'path' => \storage_path('logs/laravel.log'),
        ],
    ],
];

/*
|--------------------------------------------------------------------------
| Usage Examples for WP Mail Transport
|--------------------------------------------------------------------------
|
| 1. Enable debug in .env:
|    WP_MAIL_DEBUG=true
|    LOG_CHANNEL=daily
|    LOG_LEVEL=debug
|
| 2. Check logs in storage/logs/laravel.log or storage/logs/mail.log
|
| 3. Example log output:
|    [2024-01-15 10:30:45] local.DEBUG: [WP Mail Transport] Sending email via wp_mail()
|    {"to":["user@example.com"],"subject":"Welcome","content_type":"text/html"}
|
| 4. Use dedicated mail channel:
|    - Update LOG_CHANNEL=mail in .env
|    - Or configure in service provider:
|      config(['logging.default' => 'mail']);
|
| 5. Send critical errors to Slack:
|    - Set LOG_SLACK_WEBHOOK_URL in .env
|    - Use stack channel: LOG_STACK=daily,slack
|    - Only critical mail errors will be sent to Slack
|
*/
