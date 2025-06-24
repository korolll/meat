<?php

namespace App\Console\Commands;

use App\Jobs\ResolveClientFavoriteAssortmentVariantJob;
use Illuminate\Console\Command;

class ResolveClientPromoFavoriteAssortment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promo-favorite-assortment:resolve';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обработка для акции "Любимый продукт"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ResolveClientFavoriteAssortmentVariantJob::dispatch([], false);
    }
}
