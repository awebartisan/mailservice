<?php

declare(strict_types=1);

namespace App\Services;

interface MailClient
{
    public function sendMessage(array $parameters);

    public function sendBatch(BatchMessage $message);

    public function getFailedRecipients(BatchMessage $message, array $options = []);
}