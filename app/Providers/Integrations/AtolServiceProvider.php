<?php

namespace App\Providers\Integrations;

use App\Services\Integrations\Atol\AtolExportPriceList;
use App\Services\Integrations\Atol\AtolOnlineClient;
use App\Services\Integrations\Atol\AtolOnlineClientInterface;
use App\Services\Integrations\Atol\Contracts\AtolExportPriceListContract;
use App\Services\Management\Client\Order\Payment\Atol\AtolSellRequestGeneratorInterface;
use App\Services\Management\Client\Order\Payment\Atol\AtolSellRequestGeneratorV4;
use App\Services\Management\Client\Order\Payment\Atol\AtolSellRequestGeneratorV5;
use GuzzleHttp\Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AtolServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AtolExportPriceListContract::class, function () {
            return new AtolExportPriceList(
                app(Client::class),
                config('services.atol.export.price_list.uri')
            );
        });

        $this->app->singleton(AtolSellRequestGeneratorInterface::class, function () {
            $version = config('services.atol.online.version');
            if ($version === AtolSellRequestGeneratorV5::VERSION) {
                return app(AtolSellRequestGeneratorV5::class);
            }

            return app(AtolSellRequestGeneratorV4::class);
        });
        $this->app->bind(AtolOnlineClientInterface::class, AtolOnlineClient::class);
        $this->app->when(AtolOnlineClient::class)->needs(Client::class)->give(function () {
            $host = config('services.atol.online.base_path');
            return new Client([
                'base_uri' => $host
            ]);
        });
        $this->app->when(AtolOnlineClient::class)->needs('$config')->give(function () {
            return config('services.atol.online.config', []);
        });
        $this->app->when(AtolSellRequestGeneratorV5::class)->needs('$config')->give(function () {
            return config('services.atol.online.request_generator_conf', []);
        });
        $this->app->when(AtolSellRequestGeneratorV4::class)->needs('$config')->give(function () {
            return config('services.atol.online.request_generator_conf', []);
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            AtolExportPriceListContract::class,
        ];
    }
}
