<?php

namespace App\Services\Pepipost;

use App\Services\TemplateFormatter;

class PepipostTemplateFormatter extends TemplateFormatter
{
    public function format(string $body): string
    {
        $replacements = ['[%', '', '%]'];

        return preg_replace(self::PATTERNS, $replacements, $body);
    }
}