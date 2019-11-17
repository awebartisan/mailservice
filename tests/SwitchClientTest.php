<?php

use App\Services\MailClient;
use App\Services\Pepipost\PepipostClient;
use App\Services\Mailgun\MailgunClient;

class SwitchClientTest extends TestCase
{
    /** @test */
    public function it_switch_mail_client_based_on_request_header()
    {
        // default service mailgun
        $this->assertInstanceOf(MailgunClient::class, app(MailClient::class));

        // requested service will be pepipost based on headers
        $this->get('/v1/logs?service=pepipost', array_merge($this->authHeaders(), ['API-SERVICE' => 'pepipost']));

        $this->assertInstanceOf(PepipostClient::class, app(MailClient::class));

        // requested service will be mailgun based on headers
        $this->get('/v1/logs?service=mailgun', array_merge($this->authHeaders(), ['API-SERVICE' => 'mailgun']));

        $this->assertInstanceOf(MailgunClient::class, app(MailClient::class));
    }
}