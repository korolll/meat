<?php

namespace App\Console\Commands;

use App\Jobs\UpdateCatalogProductCountJob;
use App\Models\Catalog;
use Illuminate\Console\Command;

class CatalogCalculateProductCountCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'catalog:calculate-product-count';

    /**
     * @var string
     */
    protected $description = 'Выполняет постановку задач в очередь для первоначального расчета количества продуктов в каталогах';

    /**
     * @return void
     */
    public function handle()
    {
        Catalog::each(function ($catalog) {
            UpdateCatalogProductCountJob::dispatch($catalog->uuid);
        });

        $this->info('Jobs for update catalog products count successfully queued');
    }
}
