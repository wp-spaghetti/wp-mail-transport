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

namespace WpSpaghetti\WpMailTransport\Transport;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * WP Mail Transport for Symfony Mailer.
 *
 * This transport bridges Laravel/Symfony Mailer with WordPress's wp_mail() function,
 * allowing you to use Laravel's Mail facade while benefiting from WordPress plugins
 * like WP Mail SMTP, SendGrid, Mailgun WP plugins, and other email delivery plugins.
 *
 * Works across different Laravel+WordPress stacks including Sage themes with Acorn,
 * WP Starter, Corcel, and custom integrations.
 */
class WpMailTransport extends AbstractTransport
{
    /**
     * Debug mode flag.
     */
    private bool $debug;

    /**
     * Create a new WP Mail transport instance.
     */
    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;

        parent::__construct();
    }

    /**
     * Send the given message.
     *
     * @throws TransportException
     */
    protected function doSend(SentMessage $message): void
    {
        try {
            $email = MessageConverter::toEmail($message->getOriginalMessage());

            // Prepare headers
            $headers = $this->prepareHeaders($email);

            // Prepare attachments
            $attachments = $this->prepareAttachments($email);

            // Determine content type and body
            [$contentType, $body] = $this->prepareBody($email);

            // Add Content-Type header if HTML
            if ($contentType === 'text/html') {
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
            }

            // Prepare recipients
            $to = \array_map(fn (Address $address) => $address->getAddress(), $email->getTo());

            // Debug logging
            if ($this->debug) {
                $this->logDebug('Sending email via wp_mail()', [
                    'to' => $to,
                    'subject' => $email->getSubject(),
                    'content_type' => $contentType,
                    'headers_count' => \count($headers),
                    'attachments_count' => \count($attachments),
                ]);
            }

            // Send email via wp_mail
            $sent = \wp_mail(
                $to,
                $email->getSubject() ?? '',
                $body,
                $headers,
                $attachments
            );

            // Cleanup temporary attachment files
            $this->cleanupAttachments($attachments);

            // Check if sending was successful
            // Note: wp_mail returning true doesn't guarantee delivery,
            // but false definitely means something went wrong
            if (! $sent) {
                if ($this->debug) {
                    $this->logDebug('wp_mail() returned false - email sending failed');
                }
                throw new TransportException('Failed to send email through wp_mail()');
            }

            if ($this->debug) {
                $this->logDebug('Email sent successfully via wp_mail()');
            }
        } catch (TransportException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($this->debug) {
                $this->logDebug('Exception during email sending', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            throw new TransportException(
                \sprintf('Error sending email: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * Prepare email headers.
     *
     * @return array<string>
     */
    private function prepareHeaders(\Symfony\Component\Mime\Email $email): array
    {
        $headers = [];

        // Process all headers except ones wp_mail handles automatically
        foreach ($email->getHeaders()->all() as $header) {
            // Skip headers that wp_mail adds automatically or we handle separately
            if (\in_array($header->getName(), ['From', 'To', 'Subject', 'Content-Type'], true)) {
                continue;
            }
            $headers[] = $header->toString();
        }

        // Add From header if present
        $from = $email->getFrom();
        if (! empty($from)) {
            $from = $from[0];
            $fromHeader = 'From: ';
            if ($from->getName()) {
                $fromHeader .= $from->getName().' <'.$from->getAddress().'>';
            } else {
                $fromHeader .= $from->getAddress();
            }
            $headers[] = $fromHeader;
        }

        return $headers;
    }

    /**
     * Prepare email attachments.
     *
     * Creates temporary files for attachments that wp_mail can use.
     *
     * @return array<string>
     */
    private function prepareAttachments(\Symfony\Component\Mime\Email $email): array
    {
        $attachments = [];

        foreach ($email->getAttachments() as $attachment) {
            $tmpPath = \tempnam(\sys_get_temp_dir(), 'wp_mail_');
            if ($tmpPath === false) {
                continue;
            }

            \file_put_contents($tmpPath, $attachment->getBody());
            $attachments[] = $tmpPath;
        }

        return $attachments;
    }

    /**
     * Prepare email body and determine content type.
     *
     * @return array{string, string} [contentType, body]
     */
    private function prepareBody(\Symfony\Component\Mime\Email $email): array
    {
        $htmlBody = $email->getHtmlBody();

        if ($htmlBody !== null) {
            return ['text/html', $htmlBody];
        }

        return ['text/plain', $email->getTextBody() ?? ''];
    }

    /**
     * Cleanup temporary attachment files.
     *
     * @param  array<string>  $attachments
     */
    private function cleanupAttachments(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            @\unlink($attachment);
        }
    }

    /**
     * Log debug information.
     *
     * @param  array<string, mixed>  $context
     */
    private function logDebug(string $message, array $context = []): void
    {
        Log::debug('[WP Mail Transport] '.$message, $context);
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'wp-mail';
    }
}
