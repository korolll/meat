<?php

namespace App\Providers\Integrations;

use App\Contracts\Integrations\DaData\Suggestions\DaDataSuggestionsClientContract;
use App\Services\Integrations\DaData\Suggestions\DaDataSuggestionsClient;
use GuzzleHttp\Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DaDataServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(DaDataSuggestionsClientContract::class, function () {
            return new DaDataSuggestionsClient(
                app(Client::class),
                config('services.dadata.suggestions.api_key')
            );
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            DaDataSuggestionsClientContract::class,
        ];
    }
}
