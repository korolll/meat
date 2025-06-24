<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncProductsWithAssortmentMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assortment-matrix:sync';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизация ассортиментых матриц с продукцией магазина';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::each(function (User $user) {
            $assortmentIds = $user->products()
                ->select('assortment_uuid')
                ->distinct()
                ->pluck('assortment_uuid');

            $user->assortmentMatrix()->syncWithoutDetaching($assortmentIds);
        });
    }
}
