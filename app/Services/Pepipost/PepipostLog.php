<?php

namespace App\Services\Pepipost;

use App\Services\BaseMailLog;

class PepipostLog extends BaseMailLog
{
    public static function fromEvent($event): self
    {
        $self = new self();
        $self->originalResponse = $event;

        $self->normalizedResponse['status'] = $event['EVENT'] ?? '';
        $self->normalizedResponse['to_email'] = $event['EMAIL'] ?? '';
        $self->normalizedResponse['from_email'] = $event['FROMADDRESS'] ?? '';
        $self->normalizedResponse['unique_identifier'] = $event['X-APIHEADER'] ?? '';

        return $self;
    }
}