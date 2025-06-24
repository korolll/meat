<?php

namespace App\Providers\Models;

use App\Contracts\Models\Tag\DeleteTagContract;
use App\Contracts\Models\Tag\FindOrCreateTagContract;
use App\Services\Models\Tag\DeleteTag;
use App\Services\Models\Tag\FindOrCreateTag;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class TagServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        FindOrCreateTagContract::class => FindOrCreateTag::class,
        DeleteTagContract::class => DeleteTag::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
