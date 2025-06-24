<?php

namespace App\Jobs;

use App\Models\PriceList;
use App\Services\Integrations\Atol\Contracts\AtolExportPriceListContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportPriceListToAtol implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var PriceList
     */
    protected $priceList;

    /**
     * @param PriceList $priceList
     */
    public function __construct(PriceList $priceList)
    {
        $this->priceList = $priceList;
    }

    /**
     * @return \Carbon\CarbonInterface
     */
    public function retryUntil()
    {
        return now()->addMonth();
    }

    /**
     * @param AtolExportPriceListContract $service
     */
    public function handle(AtolExportPriceListContract $service)
    {
        try {
            $service->export($this->priceList);
        } catch (\Throwable $e) {
            report($e);
            $this->release(1800);
        }
    }
}
