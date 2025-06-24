<?php

namespace App\Providers\Integrations;

use App\Contracts\Integrations\OneC\BarcodeFormatterContract;
use App\Contracts\Integrations\OneC\CatalogExporterContract;
use App\Services\Integrations\OneC\CatalogExporter;
use App\Contracts\Integrations\OneC\PriceListExporterContract;
use App\Contracts\Integrations\OneC\ProductExporterContract;
use App\Services\Integrations\OneC\BarcodeFormatter;
use App\Services\Integrations\OneC\PriceListExporter;
use App\Services\Integrations\OneC\ProductExporter;
use App\Services\Integrations\OneC\ProductRequestExporter;
use App\Services\Integrations\OneC\ProductRequestExporterContract;
use GuzzleHttp\Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class OneCServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ProductRequestExporterContract::class, function () {
            return new ProductRequestExporter(
                app(Client::class),
                config('services.1c.product_request_exporter.uri'),
                config('services.1c.product_request_exporter.token_header'),
                config('services.1c.product_request_exporter.token'),
                app(BarcodeFormatterContract::class)
            );
        });

        $this->app->singleton(ProductExporterContract::class, function () {
            return new ProductExporter(
                app(Client::class),
                config('services.1c.product_exporter.uri'),
                config('services.1c.product_exporter.token_header'),
                config('services.1c.product_exporter.token'),
                app(BarcodeFormatterContract::class)
            );
        });

        $this->app->singleton(PriceListExporterContract::class, function () {
            return new PriceListExporter(
                app(Client::class),
                config('services.1c.price_list_exporter.uri'),
                config('services.1c.price_list_exporter.token_header'),
                config('services.1c.price_list_exporter.token'),
                app(BarcodeFormatterContract::class)
            );
        });

        $this->app->singleton(BarcodeFormatterContract::class, BarcodeFormatter::class);

        $this->app->singleton(CatalogExporterContract::class, function () {
            return new CatalogExporter(
                app(Client::class),
                config('services.1c.catalog_exporter.uri'),
                config('services.1c.catalog_exporter.token_header'),
                config('services.1c.catalog_exporter.token')
            );
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            ProductRequestExporterContract::class,
            ProductExporterContract::class,
            PriceListExporterContract::class,
            BarcodeFormatterContract::class,
            CatalogExporterContract::class,
        ];
    }
}
