<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\MailClient;
use Illuminate\Support\ServiceProvider;
use App\Repositories\MailLogsRepository;
use App\Repositories\RedisMailLogsRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(MailClient::class, function () {
            return get_mail_client();
        });

        $this->app->bind(MailLogsRepository::class, RedisMailLogsRepository::class);
    }
}
