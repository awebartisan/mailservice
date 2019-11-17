<?php

use App\Http\Middleware\ClientSwitcher;
use App\Http\Middleware\ApiAuth;
use App\Http\Middleware\VerifyMailgunWebhook;
use Laravel\Lumen\Routing\Router;

/** @var Router $router */
$router = app(Router::class);

$router->group(
    ['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\V1'],
    function (Router $router) {
        $router->group(['middleware' => [ApiAuth::class, ClientSwitcher::class]], function (Router $api) {
            $api->post('mails/batch', 'SendBatchController@handle');
            $api->post('mails/message', 'SendMailMessageController@handle');

            $api->get('logs', 'MailLogsController@index');
        });

        $router->group(['middleware' => VerifyMailgunWebhook::class], function (Router $router) {
            $router->post('logs/mailgun-webhook', 'MailgunWebhookController@handle');
        });

        $router->post('logs/pepipost-webhook', 'PepipostWebhookController@handle');

        $router->post('logs/sendgrid-webhook', 'SendGridWebhookController@handle');
    }
);


