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

namespace WpSpaghetti\WpMailTransport;

use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use WpSpaghetti\WpMailTransport\Transport\WpMailTransport;

class WpMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/wp-mail.php',
            'wp-mail'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config (optional)
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/wp-mail.php' => \config_path('wp-mail.php'),
            ], 'wp-mail-config');
        }

        // Register the WP Mail transport driver
        $this->app->resolving(MailManager::class, function (MailManager $mailManager): void {
            $mailManager->extend('wp-mail', function () {
                $debug = \config('wp-mail.debug', false);

                return new WpMailTransport($debug);
            });
        });
    }
}
