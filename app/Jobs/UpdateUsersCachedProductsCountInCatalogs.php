<?php

namespace App\Jobs;

use App\Models\Catalog;
use App\Models\User;
use App\Services\Models\Catalog\CatalogMap;
use App\Services\Models\User\ProductsInCatalogCacherInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateUsersCachedProductsCountInCatalogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $catalogs = Catalog::all();
        $map = new CatalogMap($catalogs->all());
        /** @var ProductsInCatalogCacherInterface $cacher */
        $cacher = app(ProductsInCatalogCacherInterface::class);

        User::store()->each(function (User $user) use ($map, $cacher) {
            $cacher->cache($user, $map);
        });
    }
}
