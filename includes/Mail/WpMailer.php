<?php

declare(strict_types=1);

namespace RDT\Corralon\Mail;

class WpMailer implements MailerInterface
{
    public function send(string $to, string $subject, string $body): bool
    {
        return wp_mail(
            $to,
            $subject,
            $body,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }
}
