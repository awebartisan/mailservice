<?php

use App\Services\MailClient;
use Mockery\LegacyMockInterface;
use App\Jobs\ProcessBatchMessage;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BatchMailTest extends TestCase
{
    /** @var LegacyMockInterface */
    protected $fakeClient;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->fakeClient = Mockery::mock(FakeMailClient::class);
    }

    private function paramerters(array $overrides = [])
    {
        return array_merge([
            "from" => "jhon.snow@thewall.north",
            "subject" => "Hey %recipient.first%",
            "body" => "If you wish to unsubscribe, click http://mailgun/unsubscribe/%recipient.id%",
            "recipients" => '{
                                "john.doe@example.com" : {"first": "John", "last": "Doe", "id": "1"},
                                "sally.doe@example.com": {"first": "Sally", "last": "Dpe", "id": "2"}
            }',
            "attachments" => ''
        ], $overrides);
    }

    /** @test */
    public function api_will_not_process_request_if_it_does_not_contains_signature_header()
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->post('/v1/mails/batch', []);
    }

    /** @test */
    public function an_unauthorized_client_cannot_send_batch_emails()
    {
        $this->expectException(UnauthorizedHttpException::class);

        $this->post('/v1/mails/batch', [], ['signature' => 'invalid-signature']);
    }

    /** @test */
    public function api_sends_batch_emails()
    {
        $this->app->instance(MailClient::class, $this->fakeClient);

        $response = $this->post('/v1/mails/batch', $this->paramerters(), $this->authHeaders());

        $response->assertResponseOk();

        Queue::assertPushed(ProcessBatchMessage::class, 1);
    }

    /** @test */
    public function api_can_send_more_then_1000_emails()
    {
        $this->app->instance(MailClient::class, $this->fakeClient);

        $recipients = '{';
        foreach (range(1, 1100) as $count) {
            $recipients .= '"email_' . $count;
            $recipients .= '@example.com": {"first": "John", "last": "Doe"}';
            $recipients .= $count != 1100 ? ',' : '';
        }
        $recipients .= '}';

        $response = $this->post('/v1/mails/batch', $this->paramerters(["recipients" => $recipients,]), $this->authHeaders());

        $response->assertResponseOk();

        Queue::assertPushed(ProcessBatchMessage::class, 2);
    }
}
