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

return [
    /*
    |--------------------------------------------------------------------------
    | WP Mail Transport
    |--------------------------------------------------------------------------
    |
    | This configuration file is optional. The WP Mail transport works
    | out of the box without any configuration. All settings are handled
    | through your mail configuration in config/mail.php.
    |
    | The transport uses WordPress's wp_mail() function, which means it
    | automatically works with any WordPress email plugin you have installed
    | (WP Mail SMTP, SendGrid, Mailgun, etc.).
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable debug mode to log additional information about email sending.
    | Useful for troubleshooting email delivery issues.
    |
    */
    'debug' => \env('WP_MAIL_DEBUG', false),
];
