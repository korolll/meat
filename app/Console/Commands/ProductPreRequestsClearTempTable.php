<?php

namespace App\Console\Commands;

use App\Models\ProductPreRequestCustomerSupplier;
use Illuminate\Console\Command;

class ProductPreRequestsClearTempTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product-pre-request:clear-temp-table';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка временной таблицы';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ProductPreRequestCustomerSupplier::truncate();
    }
}
