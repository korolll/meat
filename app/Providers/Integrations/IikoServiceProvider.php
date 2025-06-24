<?php


namespace App\Providers\Integrations;


use App\Services\Integrations\Iiko\IikoClient;
use App\Services\Integrations\Iiko\IikoClientInterface;
use GuzzleHttp\Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class IikoServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->bind(IikoClientInterface::class, IikoClient::class);
        $this->app->when(IikoClient::class)->needs(Client::class)->give(function () {
            $host = config('services.iiko.host');
            return new Client([
                'base_uri' => $host
            ]);
        });
        $this->app->when(IikoClient::class)->needs('$apiKey')->give(function () {
            return config('services.iiko.api_key');
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            IikoClientInterface::class,
        ];
    }
}
