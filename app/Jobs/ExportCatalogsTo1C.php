<?php

namespace App\Jobs;

use App\Contracts\Integrations\OneC\CatalogExporterContract;
use App\Models\Catalog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ExportCatalogsTo1C implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Catalog
     */
    protected $catalogs;

    /**
     * @param Collection&Catalog[] $catalogs
     */
    public function __construct(Collection $catalogs)
    {
        $this->catalogs = $catalogs;
    }

    /**
     * @return \Carbon\CarbonInterface
     */
    public function retryUntil()
    {
        return now()->addMonth();
    }

    /**
     * @param CatalogExporterContract $exporter
     */
    public function handle(CatalogExporterContract $exporter)
    {
        try {
            $exporter->export($this->catalogs);
        } catch (\Throwable $e) {
            report($e);
            $this->release(86400);
        }
    }
}
