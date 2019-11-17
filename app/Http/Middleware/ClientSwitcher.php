<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\MailClient;
use Laravel\Lumen\Application;

class ClientSwitcher
{
    /** @var Application */
    public $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->hasHeader('API-SERVICE')) {
            return $next($request);
        }

        if ($request->header('API-SERVICE') === 'mailgun') {
            $this->app->bind(MailClient::class, function () {
                return get_mail_client('mailgun');
            });
        }

        if ($request->header('API-SERVICE') === 'pepipost') {
            $this->app->bind(MailClient::class, function () {
                return get_mail_client('pepipost');
            });
        }

        if ($request->header('API-SERVICE') === 'sendgrid') {
            $this->app->bind(MailClient::class, function () {
                return get_mail_client('sendgrid');
            });
        }

        // if no service matches from header then it will
        // fallback to default mail client setup in env

        return $next($request);
    }
}
