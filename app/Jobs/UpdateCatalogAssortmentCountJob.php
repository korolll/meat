<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class UpdateCatalogAssortmentCountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        DB::select("select update_catalog_assortment_count(?)", [$this->catalogUuid]);
    }
}
