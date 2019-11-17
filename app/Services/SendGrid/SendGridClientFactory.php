<?php

namespace App\Services\SendGrid;

use App\Repositories\MailLogsRepository;
use App\Services\MailClient;

class SendGridClientFactory
{
    public static function make(): MailClient
    {
        return new SendGridClient(
            new \SendGrid(config('mail.sendgrid.api_key')),
            app(MailLogsRepository::class)
        );
    }
}