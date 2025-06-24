<?php

namespace App\Providers\Models;

use App\Contracts\Models\Draft\CreateDraftContract;
use App\Contracts\Models\Draft\DeleteDraftByNameContract;
use App\Contracts\Models\Draft\FindDraftByNameContract;
use App\Services\Models\Draft\CreateDraft;
use App\Services\Models\Draft\DeleteDraftByName;
use App\Services\Models\Draft\FindDraftByName;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DraftServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @var array
     */
    public $singletons = [
        CreateDraftContract::class => CreateDraft::class,
        DeleteDraftByNameContract::class => DeleteDraftByName::class,
        FindDraftByNameContract::class => FindDraftByName::class,
    ];

    /**
     * @return array
     */
    public function provides()
    {
        return array_keys($this->singletons);
    }
}
