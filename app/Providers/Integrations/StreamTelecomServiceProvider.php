<?php

namespace App\Providers\Integrations;

use App\Services\Integrations\StreamTelecom\StreamTelecomClient;
use GuzzleHttp\Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class StreamTelecomServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StreamTelecomClient::class, function () {
            return new StreamTelecomClient(
                app(Client::class),
                config('services.stream-telecom.username'),
                config('services.stream-telecom.password'),
                config('services.stream-telecom.from')
            );
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            StreamTelecomClient::class,
        ];
    }
}
