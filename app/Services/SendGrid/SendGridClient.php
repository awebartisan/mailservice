<?php

namespace App\Services\SendGrid;

use Illuminate\Support\Arr;
use SendGrid\Mail\Attachment;
use SendGrid\Mail\Mail;
use App\Services\MailClient;
use App\Services\BatchMessage;
use App\Services\TemplateFormatter;
use App\Repositories\MailLogsRepository;

class SendGridClient implements MailClient
{
    /** @var \SendGrid */
    private $sendGridClient;

    /** @var MailLogsRepository */
    private $logsRepository;

    public function __construct(\SendGrid $sendGridClient, MailLogsRepository $logsRepository)
    {
        $this->sendGridClient = $sendGridClient;
        $this->logsRepository = $logsRepository;
    }

    public function sendMessage(array $parameters): void
    {
        $attachment = get_attachment_from_url($parameters['attachment']);

        $email = new Mail();

        $email->setFrom($parameters['from']);
        $email->addTo($parameters['to']);
        $email->setSubject($parameters['subject']);
        $email->addContent('text/plain', $parameters['body']);

        if (isset($parameters['attachment'])) {
            $email->addAttachment(new Attachment(
                file_get_contents($attachment),
                mime_content_type($attachment),
                get_filename($parameters['attachment'])
            ));
        }

        try {
            $this->sendGridClient->send($email);
        } finally {
            if (file_exists($attachment)) {
                unlink($attachment);
            }
        }
    }

    public function sendBatch(BatchMessage $message)
    {
        $batch = new Mail();

        $message->setBatchIdentifier(md5(microtime().rand()));

        $batch->setBatchId($message->batchIdentifier());

        $batch->setFrom($message->from());
        $batch->setSubject($message->subject(TemplateFormatter::SENDGRID));
        $batch->addContent('text/plain', $message->body(TemplateFormatter::SENDGRID));

        foreach ($message->recipients() as $recipient) {
            $email = $recipient['email'];
            $name = strtok($email, '@');
            $batch->addTo($email, $name, $this->mapKeys(Arr::except($recipient, 'email')));
        }

        foreach ($message->attachments() as $attachment) {
            $batch->addAttachment(base64_encode(file_get_contents($attachment)));
        }

        $this->sendGridClient->send($batch);
    }

    public function getFailedRecipients(BatchMessage $message, array $options = [])
    {
        $logs = $this->logsRepository->getLogs(0, -1, 'sendgrid_failed:logs');

        $failedRecipients = collect($logs)->map(function ($log) {
            return data_get(json_decode($log, true), 'normalized_response.to_email');
        })
        ->filter(function ($email) use ($message) {
            return in_array($email, $message->recipients());
        });

        $message->setToRecipients($failedRecipients->toArray());

        // delete failed logs next time failed logs list does not
        // contains failed logs from previous sent batch
        $this->logsRepository->deleteKey('sendgrid_failed:logs');

        return $message;
    }

    public function mapKeys(array $attributes): array
    {
        return collect($attributes)->mapWithKeys(function ($value, $key) {
            return ["%{$key}%" => $value];
        })->toArray();
    }
}
