<?php

namespace App\Services\Mailgun;

use App\Services\TemplateFormatter;

class MailgunTemplateFormatter extends TemplateFormatter
{
    public function format(string $body): string
    {
        $replacements = ['%', 'recipient.', '%'];

        return preg_replace(self::PATTERNS, $replacements, $body);
    }
}