<?php

declare(strict_types=1);

namespace App\Services\Mailgun;

use App\Batch;
use App\Services\MailClient;
use App\Services\TemplateFormatter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Mailgun\Mailgun;
use App\Services\BatchMessage;
use Mailgun\Message\Exceptions\MissingRequiredParameter;
use Mailgun\Message\Exceptions\TooManyRecipients;
use Mailgun\Model\Event\Event;

class MailgunClient implements MailClient
{
    /** @var Mailgun */
    protected $mailgun;

    /** @var string */
    protected $domain;

    public function __construct(Mailgun $mailgun, string $domain)
    {
        $this->domain = $domain;
        $this->mailgun = $mailgun;
    }

    /**
     * Send batch emails
     *
     * @param BatchMessage $message
     *
     * @return void
     * @throws MissingRequiredParameter
     */
    public function sendBatch(BatchMessage $message)
    {
        /** @var \Mailgun\Message\BatchMessage $batchMessage */
        $batchMessage = $this->mailgun->messages()
            ->getBatchMessage($this->domain)
            ->setFromAddress($message->from())
            ->setSubject($message->subject(TemplateFormatter::MAILGUN))
            ->setTextBody($message->body(TemplateFormatter::MAILGUN));

        foreach ($message->attachments() as $attachment) {
            $batchMessage->addAttachment($attachment);
        }

        foreach ($message->recipients() as $recipient) {
            try {
                $batchMessage->addToRecipient($recipient['email'], Arr::except($recipient, 'email'));
            } catch (TooManyRecipients $exception) {
                continue;
            }
        }

        $batchMessage->finalize();

        [$messageId] = $batchMessage->getMessageIds();

        $message->setBatchIdentifier(trim($messageId, '<>'));

        Batch::record($message, 'mailgun');
    }

    public function sendMessage(array $parameters)
    {
        $attachment = get_attachment_from_url($parameters['attachment']);

        $message = collect($parameters)->mapWithKeys(function ($value, $key) use ($attachment) {
            if ($key === 'body') {
                return ['text' => $value];
            }

            if ($key === 'attachment') {
                return [$key => [[
                    'filePath' => $attachment,
                    'filename' => get_filename($value)
                ]]];
            }

            return [$key => $value];
        });

        try {
            $this->mailgun->messages()->send($this->domain, $message->toArray());
        } finally {
            if (isset($attachment) && file_exists($attachment)) {
                unlink($attachment);
            }
        }
    }

    public function getEvents(array $query = [])
    {
        return $this->mailgun->events()->get($this->domain, $query);
    }

    public function getFailedRecipients(BatchMessage $batchMessage, array $options = [])
    {
        $response =  $this->getEvents([
            'event' => 'rejected OR failed',
            'limit' =>  count($batchMessage->recipients()),
            'message-id' => $batchMessage->batchIdentifier(),
        ]);

        $failedRecipients = collect($response->getItems())->map(function (Event $event) {
            return $event->getEnvelope()['targets']
                ?? $event->getMessage()['headers']['to']
                ?? $event->getRecipient() ?? '';
        });

        if ($failedRecipients->isEmpty()) {
            throw new \RuntimeException('No failed recipients found against given batch message');
        }

        $recipients = collect($batchMessage->recipients())->reject(function ($attributes, $email) use ($failedRecipients) {
            return ! in_array($email, $failedRecipients->toArray());
        });

        $batchMessage->setToRecipients($recipients->toArray());

        return $batchMessage;
    }
}
