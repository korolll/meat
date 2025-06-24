<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class UpdateCatalogProductCountJob implements ShouldQueue
{
    use Dispatchable;

    /**
     * @var string
     */
    protected $catalogUuid;

    /**
     * Create a new job instance.
     * @param string $catalogUuid
     * @return void
     */
    public function __construct(string $catalogUuid)
    {
        $this->catalogUuid = $catalogUuid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::select("select update_catalog_product_count(?)", [$this->catalogUuid]);
    }
}
