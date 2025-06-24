<?php

namespace App\Services\Integrations\OneC;

use App\Contracts\Integrations\OneC\BarcodeFormatterContract;
use App\Models\Product;
use App\Models\ProductRequest;
use GuzzleHttp\Client;

class ProductRequestExporterTest extends ProductRequestExporter
{
    /**
     * @param ProductRequest $productRequest
     * @return bool
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function export(ProductRequest $productRequest): bool
    {
        return true;
    }
}
