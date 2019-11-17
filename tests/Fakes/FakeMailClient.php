<?php

use App\Services\BatchMessage;
use App\Services\MailClient;

class FakeMailClient implements MailClient
{
    public function sendMessage(array $parameters)
    {
    }

    public function sendBatch(BatchMessage $message)
    {
        return ['123@domain', '456@domain'];
    }

    public function getFailedRecipients(BatchMessage $message, array $options = [])
    {
    }
}