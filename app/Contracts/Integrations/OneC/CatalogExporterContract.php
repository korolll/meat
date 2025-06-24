<?php

namespace App\Contracts\Integrations\OneC;

use App\Models\Catalog;
use Illuminate\Support\Collection;


interface CatalogExporterContract
{
    /**
     * @param Collection&Catalog[] $catalogs
     * @return bool
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function export(Collection $catalogs): bool;
}
