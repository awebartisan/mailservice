<?php

namespace App\Services;

use App\Services\Mailgun\MailgunTemplateFormatter;
use App\Services\Pepipost\PepipostTemplateFormatter;
use App\Services\SendGrid\SendGridTemplateFormatter;

abstract class TemplateFormatter
{
    public const MAILGUN = MailgunTemplateFormatter::class;
    public const PEPIPOST = PepipostTemplateFormatter::class;
    public const SENDGRID = SendGridTemplateFormatter::class;

    protected const PATTERNS = ['/<\s*%/', '/attribute./', '/%\s*>/'];

    abstract public function format(string $body): string;
}