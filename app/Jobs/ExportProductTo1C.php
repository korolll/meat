<?php

namespace App\Jobs;

use App\Contracts\Integrations\OneC\ProductExporterContract;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportProductTo1C implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return \Carbon\CarbonInterface
     */
    public function retryUntil()
    {
        return now()->addMonth();
    }

    /**
     * @param ProductExporterContract $exporter
     */
    public function handle(ProductExporterContract $exporter)
    {
        try {
            $exporter->export($this->product);
        } catch (\Throwable $e) {
            report($e);
            $this->release(86400);
        }
    }
}
