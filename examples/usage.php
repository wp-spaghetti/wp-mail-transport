<?php

/**
 * WP Mail Transport - Usage Examples
 *
 * This file demonstrates various ways to use the package in your Laravel+WordPress project.
 */

declare(strict_types=1);

/*
 * This file is part of the WP Mail Transport package.
 *
 * (ɔ) Frugan <dev@frugan.it>
 *
 * This source file is subject to the GNU GPLv3 license that is bundled
 * with this source code in the file COPYING.
 */

namespace App\Examples;

use Illuminate\Support\Facades\Mail;

class MailExamples
{
    /**
     * Basic email sending
     */
    public function basicEmail(): void
    {
        // Simple text email
        Mail::raw('This is a test email', function ($message): void {
            $message->to('user@example.com')
                ->subject('Test Email');
        });

        // HTML email
        Mail::send('emails.welcome', ['name' => 'John'], function ($message): void {
            $message->to('user@example.com')
                ->subject('Welcome!');
        });
    }

    /**
     * Email with multiple recipients
     */
    public function multipleRecipients(): void
    {
        Mail::raw('Important update', function ($message): void {
            $message->to('user1@example.com')
                ->cc(['user2@example.com', 'user3@example.com'])
                ->bcc('admin@example.com')
                ->subject('Important Update');
        });
    }

    /**
     * Email with attachments
     */
    public function emailWithAttachments(): void
    {
        $pdfPath = \storage_path('app/invoice.pdf');

        Mail::raw('Please find your invoice attached', function ($message) use ($pdfPath): void {
            $message->to('customer@example.com')
                ->subject('Your Invoice')
                ->attach($pdfPath);
        });

        // Multiple attachments
        Mail::raw('Documents attached', function ($message): void {
            $message->to('user@example.com')
                ->subject('Documents')
                ->attach(\storage_path('app/doc1.pdf'))
                ->attach(\storage_path('app/doc2.pdf'));
        });
    }

    /**
     * Using Mailables
     */
    public function usingMailables(): void
    {
        $user = \App\Models\User::find(1);

        // Send using mailable
        Mail::to($user->email)->send(new \App\Mail\WelcomeEmail($user));

        // Queue email for later sending
        Mail::to($user->email)->queue(new \App\Mail\NewsletterEmail);
    }

    /**
     * Custom headers
     */
    public function customHeaders(): void
    {
        Mail::raw('Email with custom headers', function ($message): void {
            $message->to('user@example.com')
                ->subject('Custom Headers')
                ->getHeaders()
                ->addTextHeader('X-Custom-Header', 'custom-value')
                ->addTextHeader('X-Priority', '1');
        });
    }

    /**
     * Reply-To address
     */
    public function replyTo(): void
    {
        Mail::raw('Email with reply-to', function ($message): void {
            $message->to('user@example.com')
                ->replyTo('support@example.com', 'Support Team')
                ->subject('Contact Support');
        });
    }

    /**
     * WordPress integration examples
     */
    public function wordpressIntegration(): void
    {
        // Send email on new user registration
        \add_action('user_register', function ($userId): void {
            $user = \get_userdata($userId);

            Mail::to($user->user_email)->send(
                new \App\Mail\WelcomeEmail($user)
            );
        });

        // Send email when post is published
        \add_action('publish_post', function ($postId, $post): void {
            $author = \get_userdata($post->post_author);

            Mail::to($author->user_email)
                ->send(new \App\Mail\PostPublished($post));
        }, 10, 2);

        // Send email on comment approval
        \add_action('comment_post', function ($commentId, $approved): void {
            if ($approved) {
                $comment = \get_comment($commentId);

                Mail::to($comment->comment_author_email)
                    ->send(new \App\Mail\CommentApproved($comment));
            }
        }, 10, 2);
    }

    /**
     * WooCommerce integration
     */
    public function woocommerceIntegration(): void
    {
        // Send custom email on order creation
        \add_action('woocommerce_new_order', function ($orderId): void {
            $order = \wc_get_order($orderId);

            Mail::to('admin@example.com')
                ->send(new \App\Mail\NewOrderNotification($order));
        });

        // Send email on order status change
        \add_action('woocommerce_order_status_changed', function ($orderId, $oldStatus, $newStatus): void {
            $order = \wc_get_order($orderId);

            if ($newStatus === 'completed') {
                Mail::to($order->get_billing_email())
                    ->send(new \App\Mail\OrderCompleted($order));
            }
        }, 10, 3);
    }

    /**
     * Conditional mailer usage
     */
    public function conditionalMailer(): void
    {
        // Use WP Mail transport by default
        Mail::to('user@example.com')->send(new \App\Mail\Newsletter);

        // Use SMTP for specific emails
        Mail::mailer('smtp')
            ->to('vip@example.com')
            ->send(new \App\Mail\VIPNewsletter);
    }

    /**
     * Bulk email sending
     */
    public function bulkEmails(): void
    {
        $users = \App\Models\User::where('subscribed', true)->get();

        foreach ($users as $user) {
            Mail::to($user->email)
                ->queue(new \App\Mail\MonthlyNewsletter($user));
        }
    }

    /**
     * Testing email in development
     */
    public function testingEmails(): void
    {
        // In development, you might want to override recipient
        if (\app()->environment('local')) {
            Mail::alwaysTo('developer@example.com');
        }

        // Now all emails go to developer@example.com in local env
        Mail::to('anyone@example.com')->send(new \App\Mail\TestEmail);
    }

    /**
     * Debug mode for troubleshooting
     */
    public function debugMode(): void
    {
        // Enable debug mode in .env:
        // WP_MAIL_DEBUG=true
        // LOG_LEVEL=debug

        // Or programmatically:
        \config(['wp-mail.debug' => true]);

        // Send test email - debug info will be logged to Laravel logs
        Mail::to('test@example.com')
            ->send(new \App\Mail\TestEmail);

        // Check logs in storage/logs/laravel.log
    }

    /**
     * Custom from address per email
     */
    public function customFromAddress(): void
    {
        Mail::to('user@example.com')
            ->from('noreply@example.com', 'No Reply')
            ->send(new \App\Mail\AutomatedEmail);

        // Different sender for different types
        Mail::to('customer@example.com')
            ->from('support@example.com', 'Support Team')
            ->send(new \App\Mail\SupportEmail);
    }

    /**
     * Email with inline images
     */
    public function inlineImages(): void
    {
        Mail::send('emails.newsletter', [], function ($message): void {
            $message->to('user@example.com')
                ->subject('Newsletter')
                ->embed(\public_path('images/logo.png'), 'logo');
        });
    }

    /**
     * Email notifications
     */
    public function notifications(): void
    {
        $user = \App\Models\User::find(1);

        // Using Laravel notifications
        $user->notify(new \App\Notifications\InvoicePaid($invoice));
    }

    /**
     * Markdown emails
     */
    public function markdownEmails(): void
    {
        Mail::to('user@example.com')
            ->send(new \App\Mail\MarkdownEmail);
    }
}
