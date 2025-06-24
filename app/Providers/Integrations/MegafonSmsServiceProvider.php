<?php


namespace App\Providers\Integrations;


use App\Services\Integrations\Megafon\MegafonSmsClient;
use GuzzleHttp\Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use function app;
use function config;

class MegafonSmsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MegafonSmsClient::class, function () {
            return new MegafonSmsClient(
                app(Client::class),
                config('services.megafon-sms.username'),
                config('services.megafon-sms.password'),
                config('services.megafon-sms.from')
            );
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            MegafonSmsClient::class,
        ];
    }
}