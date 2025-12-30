![PHP Version](https://img.shields.io/packagist/php-v/wp-spaghetti/wp-mail-transport)
![Packagist Downloads](https://img.shields.io/packagist/dt/wp-spaghetti/wp-mail-transport)
![Packagist Stars](https://img.shields.io/packagist/stars/wp-spaghetti/wp-mail-transport)
![GitHub Actions Workflow Status](https://github.com/wp-spaghetti/wp-mail-transport/actions/workflows/release.yml/badge.svg)
![Coverage Status](https://img.shields.io/codecov/c/github/wp-spaghetti/wp-mail-transport)
![Known Vulnerabilities](https://snyk.io/test/github/wp-spaghetti/wp-mail-transport/badge.svg)
![GitHub Issues](https://img.shields.io/github/issues/wp-spaghetti/wp-mail-transport)
![GitHub Release](https://img.shields.io/github/v/release/wp-spaghetti/wp-mail-transport)
![License](https://img.shields.io/github/license/wp-spaghetti/wp-mail-transport)
<!--
Qlty @see https://github.com/badges/shields/issues/11192
![GitHub Downloads (all assets, all releases)](https://img.shields.io/github/downloads/wp-spaghetti/wp-mail-transport/total)
![Code Climate](https://img.shields.io/codeclimate/maintainability/wp-spaghetti/wp-mail-transport)
![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen)
-->

# WP Mail Transport

Symfony Mailer transport that uses WordPress's `wp_mail()` function, allowing you to use Laravel's Mail facade while leveraging WordPress email plugins.

Works with any Laravel + WordPress setup: [Acorn](https://github.com/roots/acorn) (w/wo [Sage](https://github.com/roots/sage)), [WP Starter](https://wpstarter.dev/), [Corcel](https://github.com/corcel/corcel), or custom integrations.

## Features

- **Clean Laravel Syntax** - Use `Mail::to()`, `Mail::send()`, etc. anywhere in your code
- **WordPress Plugin Support** - Works seamlessly with WP Mail SMTP, SendGrid, Mailgun, and other WP email plugins
- **Zero Configuration** - Works out of the box with sensible defaults
- **Framework Agnostic** - Not tied to Acorn, works across different Laravel+WordPress stacks
- **Full Symfony Mailer API** - Support for attachments, HTML emails, CC, BCC, custom headers

## Requirements

- PHP >= 8.2
- WordPress >= 6.0
- Laravel Illuminate/Support ^10.0|^11.0|^12.0
- Symfony Mailer ^6.0|^7.0

## Installation

### 1. Install the package

In your Laravel + WordPress project (Sage theme, WP Starter, etc.):

```bash
composer require wp-spaghetti/wp-mail-transport
```

The package auto-registers via service provider discovery.

### 2. Configure mail transport

Update your `config/mail.php`:

```php
<?php

return [
    'default' => env('MAIL_MAILER', 'wp-mail'),

    'mailers' => [
        'wp-mail' => [
            'transport' => 'wp-mail',
        ],

        // Keep other transports for fallback
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],
];
```

### 3. Set environment variable

Update your `.env`:

```env
MAIL_MAILER=wp-mail
```

That's it! The transport will now use WordPress's `wp_mail()` function.

## Usage

### Basic Email

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Email body content', function ($message) {
    $message->to('user@example.com')
            ->subject('Test Email');
});
```

### Using Mailables

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

Mail::to($user->email)->send(new WelcomeEmail($user));
```

### HTML Emails

```php
Mail::send('emails.welcome', ['user' => $user], function ($message) use ($user) {
    $message->to($user->email)
            ->subject('Welcome to our application');
});
```

### With Attachments

```php
Mail::send('emails.invoice', $data, function ($message) use ($user, $pdf) {
    $message->to($user->email)
            ->subject('Your Invoice')
            ->attach($pdf);
});
```

### Multiple Recipients

```php
Mail::to('user1@example.com')
    ->cc('user2@example.com')
    ->bcc('admin@example.com')
    ->send(new OrderShipped($order));
```

### Custom Headers

```php
Mail::raw('Email body', function ($message) {
    $message->to('user@example.com')
            ->subject('Test')
            ->getHeaders()
            ->addTextHeader('X-Custom-Header', 'value');
});
```

## Framework-Specific Examples

### Sage Themes (Acorn)

```php
// In app/Controllers/ContactController.php
use Illuminate\Support\Facades\Mail;

public function submit(Request $request)
{
    Mail::to('info@example.com')->send(
        new ContactFormSubmission($request->all())
    );
    
    return back()->with('success', 'Message sent!');
}
```

### WP Starter

```php
// In your custom plugins or theme
use Illuminate\Support\Facades\Mail;

add_action('user_register', function($user_id) {
    $user = get_userdata($user_id);
    
    Mail::to($user->user_email)->send(
        new WelcomeEmail($user)
    );
});
```

### Corcel

```php
use Corcel\Model\User;
use Illuminate\Support\Facades\Mail;

$users = User::published()->get();

foreach ($users as $user) {
    Mail::to($user->user_email)->send(
        new NewsletterEmail($user)
    );
}
```

## How It Works

### Architecture

```
Laravel Mail::to()->send()
    ↓
Symfony Mailer
    ↓
WpMailTransport
    ↓
wp_mail()
    ↓
WordPress Email Plugins
    ├─ WP Mail SMTP
    ├─ SendGrid
    ├─ Mailgun
    ├─ Amazon SES
    └─ Other plugins
    ↓
Email Delivery
```

### Technical Details

**Email Conversion:**
- Converts Symfony Message to Email object
- Extracts headers, recipients, subject, body
- Handles HTML and plain text bodies
- Processes attachments

**Header Handling:**
- Filters out headers that `wp_mail()` adds automatically (`From`, `To`, `Subject`, `Content-Type`)
- Preserves custom headers (CC, BCC, Reply-To, X-* headers)
- Properly formats From header with name and address

**Attachment Processing:**
- Creates temporary files for attachments
- Passes file paths to `wp_mail()`
- Cleans up temporary files after sending

**Error Handling:**
- Throws `TransportException` if sending fails
- Proper exception chaining for debugging
- Graceful cleanup on errors

## WordPress Plugin Integration

The transport automatically works with popular WordPress email plugins:

### WP Mail SMTP

Configure WP Mail SMTP in WordPress admin, then use Laravel's Mail facade normally:

```php
Mail::to('user@example.com')->send(new OrderConfirmation($order));
// Uses WP Mail SMTP configuration automatically
```

### SendGrid Plugin

Install and configure the SendGrid WordPress plugin:

```php
Mail::to($user->email)->send(new WelcomeEmail($user));
// Sends through SendGrid via WordPress plugin
```

### Mailgun Plugin

Configure Mailgun in WordPress, then:

```php
Mail::to($subscribers)->send(new Newsletter($content));
// Routes through Mailgun WordPress plugin
```

## Advanced Configuration

### Publish Configuration (Optional)

```bash
# Sage/Acorn
wp acorn vendor:publish --tag=wp-mail-config

# WP Starter (using Laravel's artisan)
php vendor/bin/wp-starter vendor:publish --tag=wp-mail-config
```

This creates `config/wp-mail.php` with debugging options:

```php
<?php

return [
    'debug' => env('WP_MAIL_DEBUG', false),
];
```

### Debug Mode

Enable debug logging to troubleshoot email delivery issues:

**In .env:**
```env
WP_MAIL_DEBUG=true
```

**Or in config/wp-mail.php:**
```php
'debug' => true,
```

**What gets logged:**
- Email recipients
- Subject line
- Content type (HTML or plain text)
- Number of headers
- Number of attachments
- wp_mail() success/failure status
- Exception details if sending fails

**Logging System:**
Debug messages are logged using Laravel's `Log` facade at the `debug` level. Configure logging in `config/logging.php`:

```php
// Log to daily files
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug', // Make sure debug level is included
        'days' => 14,
    ],
],
```

**Log Format:**
Laravel automatically formats logs with timestamp and context:

```
[2024-01-15 10:30:45] local.DEBUG: [WP Mail Transport] Sending email via wp_mail() {"to":["user@example.com"],"subject":"Order Confirmation","content_type":"text/html","headers_count":2,"attachments_count":1}
[2024-01-15 10:30:45] local.DEBUG: [WP Mail Transport] Email sent successfully via wp_mail()
```

**Log Channels:**
You can specify different log channels in `config/logging.php`:
- `single` - Single file
- `daily` - Daily rotating files
- `slack` - Send to Slack
- `syslog` - System log
- `errorlog` - PHP error log
- `stack` - Multiple channels

**Note:** Debug mode logs sensitive information (email addresses). Only enable in development or when troubleshooting specific issues.

### Multiple Mailers

You can use multiple transports in the same application:

```php
// config/mail.php
'mailers' => [
    'wp-mail' => [
        'transport' => 'wp-mail',
    ],
    'smtp' => [
        'transport' => 'smtp',
        // ... SMTP config
    ],
],
```

Then switch between them:

```php
// Use WP Mail transport (default)
Mail::to($user)->send(new Welcome($user));

// Use SMTP directly
Mail::mailer('smtp')->to($admin)->send(new Alert($data));
```

### Custom From Address

Set globally in `config/mail.php`:

```php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
    'name' => env('MAIL_FROM_NAME', 'My Application'),
],
```

Or per-email:

```php
Mail::to($user)
    ->from('custom@example.com', 'Custom Sender')
    ->send(new CustomEmail());
```

## Troubleshooting

### Emails not being sent

1. **Check if wp_mail() is working:**

```php
$result = wp_mail('test@example.com', 'Test', 'Test message');
var_dump($result); // Should be true
```

2. **Check WordPress email plugins:**
   - Ensure WP Mail SMTP or similar plugin is configured
   - Test sending from WordPress admin

3. **Enable debug mode:**

```env
WP_MAIL_DEBUG=true
LOG_LEVEL=debug
```

With debug mode enabled, the transport will log detailed information about each email using Laravel's logging system. Check your logs in `storage/logs/laravel.log`:

**What gets logged:**
- Recipients
- Subject
- Content type (HTML/plain text)
- Number of headers
- Number of attachments
- Success/failure status

**Example debug output:**
```
[2024-01-15 10:30:45] local.DEBUG: [WP Mail Transport] Sending email via wp_mail() {"to":["user@example.com"],"subject":"Welcome","content_type":"text/html","headers_count":3,"attachments_count":0}
[2024-01-15 10:30:45] local.DEBUG: [WP Mail Transport] Email sent successfully via wp_mail()
```

**Configure logging channels in `config/logging.php`** to send logs to different destinations (files, Slack, syslog, etc.).

### Attachments not working

Ensure your WordPress upload directory is writable:

```php
$upload_dir = wp_upload_dir();
echo $upload_dir['basedir']; // Should be writable
```

### Custom headers not appearing

Some WordPress email plugins may strip custom headers. Check your plugin's settings or filters.

### From address being overridden

WordPress plugins may force a specific From address. Check:

```php
// In your WordPress code
add_filter('wp_mail_from', function($email) {
    return 'your-desired@example.com';
});
```

## Testing

```bash
composer test
```

## Comparison with Other Solutions

| Feature | wp-mail-transport | roots/acorn-mail | Others |
|---------|------------------|------------------|---------|
| Framework Support | ✅ Acorn, WP Starter, Corcel, Custom | ❌ Acorn only | ❌ Usually Acorn only |
| WordPress Plugin Integration | ✅ Yes | ❌ No (SMTP only) | ⚠️ Limited |
| Configuration Required | ✅ Minimal | ⚠️ SMTP setup needed | ⚠️ Varies |
| Attachment Support | ✅ Yes | ✅ Yes | ✅ Yes |
| HTML Email Support | ✅ Yes | ✅ Yes | ✅ Yes |
| Maintained | ✅ Active | ✅ Active | ⚠️ Varies |

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for a detailed list of changes for each release.

We follow [Semantic Versioning](https://semver.org/) and use [Conventional Commits](https://www.conventionalcommits.org/) to automatically generate our changelog.

### Release Process

- **Major versions** (1.0.0 → 2.0.0): Breaking changes
- **Minor versions** (1.0.0 → 1.1.0): New features, backward compatible
- **Patch versions** (1.0.0 → 1.0.1): Bug fixes, backward compatible

All releases are automatically created when changes are pushed to the `main` branch, based on commit message conventions.

## Contributing

For your contributions please use:

- [Conventional Commits](https://www.conventionalcommits.org)
- [Pull request workflow](https://docs.github.com/en/get-started/exploring-projects-on-github/contributing-to-a-project)

See [CONTRIBUTING](.github/CONTRIBUTING.md) for detailed guidelines.

## Sponsor

[<img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" width="200" alt="Buy Me A Coffee">](https://buymeacoff.ee/frugan)

## License

(ɔ) Copyleft 2026 [Frugan](https://frugan.it).  
[GNU GPLv3](https://choosealicense.com/licenses/gpl-3.0/), see [LICENSE](LICENSE) file.
