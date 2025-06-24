<?php

namespace App\Jobs;

use App\Models\PriceList;
use App\Contracts\Integrations\OneC\PriceListExporterContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportPriceListTo1C implements ShouldQueue
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
     * @param PriceListExporterContract $exporter
     */
    public function handle(PriceListExporterContract $exporter)
    {
        try {
            $exporter->export($this->priceList);
        } catch (\Throwable $e) {
            report($e);
            $this->release(86400);
        }
    }
}
