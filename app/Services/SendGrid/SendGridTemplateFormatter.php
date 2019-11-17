<?php

namespace App\Services\SendGrid;

use App\Services\TemplateFormatter;

class SendGridTemplateFormatter extends TemplateFormatter
{
    public function format(string $body): string
    {
        $replacements = ['%', '', '%'];

        return preg_replace(self::PATTERNS, $replacements, $body);
    }
}