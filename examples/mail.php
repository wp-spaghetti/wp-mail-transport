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
 * Mail Configuration Example for WP Mail Transport
 *
 * This is an example configuration file showing how to set up the wp-mail transport
 * alongside other mail transports in your Laravel + WordPress project.
 *
 * Copy this configuration to your config/mail.php file.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured below.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin", "wp-mail"
    |
    */
    'default' => \env('MAIL_MAILER', 'wp-mail'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    */
    'mailers' => [
        // WP Mail Transport - Uses WordPress wp_mail() function
        // This automatically uses any WordPress email plugin you have configured
        // (WP Mail SMTP, SendGrid, Mailgun, etc.)
        'wp-mail' => [
            'transport' => 'wp-mail',
        ],

        // SMTP Transport - Direct SMTP configuration
        'smtp' => [
            'transport' => 'smtp',
            'url' => \env('MAIL_URL'),
            'host' => \env('MAIL_HOST', '127.0.0.1'),
            'port' => \env('MAIL_PORT', 2525),
            'encryption' => \env('MAIL_ENCRYPTION', 'tls'),
            'username' => \env('MAIL_USERNAME'),
            'password' => \env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => \env('MAIL_EHLO_DOMAIN', \parse_url(\env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        // Amazon SES
        'ses' => [
            'transport' => 'ses',
        ],

        // Postmark
        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        // Resend
        'resend' => [
            'transport' => 'resend',
        ],

        // Sendmail
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => \env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        // Log - Useful for development
        'log' => [
            'transport' => 'log',
            'channel' => \env('MAIL_LOG_CHANNEL'),
        ],

        // Array - Useful for testing
        'array' => [
            'transport' => 'array',
        ],

        // Failover - Try multiple transports
        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'wp-mail',
                'smtp',
            ],
        ],

        // Round Robin - Distribute emails across transports
        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'wp-mail',
                'smtp',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */
    'from' => [
        'address' => \env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => \env('MAIL_FROM_NAME', 'Example'),
    ],
];
