<?php

namespace App\Console\Commands;

use App\Models\Catalog;
use Illuminate\Console\Command;

class CatalogCalculateAssortmentCountCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'catalog:calculate-assortment-count';

    /**
     * @var string
     */
    protected $description = 'Выполняет постановку задач в очередь для первоначального расчета количества ассортиментов в каталогах';

    /**
     * @return void
     */
    public function handle()
    {
        Catalog::each(function ($catalog) {
            \App\Jobs\UpdateCatalogAssortmentCountJob::dispatch($catalog->uuid);
        });

        $this->info('Jobs for update catalog assortments count successfully queued');
    }
}
