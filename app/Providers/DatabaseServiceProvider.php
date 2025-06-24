<?php

namespace App\Providers;

use App\Contracts\Database\ToQueryTransformerContract;
use App\Services\Database\PhraseToQueryTransformer;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        ToQueryTransformerContract::class => PhraseToQueryTransformer::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
