<?php

declare(strict_types=1);

namespace RDT\Corralon\Mail;

interface MailerInterface
{
    public function send(string $to, string $subject, string $body): bool;
}
