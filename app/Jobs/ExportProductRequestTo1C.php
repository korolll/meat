<?php

namespace App\Jobs;

use App\Models\ProductRequest;
use App\Services\Integrations\OneC\ProductRequestExporterContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportProductRequestTo1C implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ProductRequest
     */
    protected $productRequest;

    /**
     * @param ProductRequest $productRequest
     */
    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest;
    }

    /**
     * @return \Carbon\CarbonInterface
     */
    public function retryUntil()
    {
        return now()->addMonth();
    }

    /**
     * @param ProductRequestExporterContract $exporter
     */
    public function handle(ProductRequestExporterContract $exporter)
    {
        try {
            $exporter->export($this->productRequest);
        } catch (\Throwable $e) {
            report($e);
            $this->release(86400);
        }
    }
}
