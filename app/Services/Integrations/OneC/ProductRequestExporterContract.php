<?php

namespace App\Services\Integrations\OneC;

use App\Models\ProductRequest;

interface ProductRequestExporterContract
{
    /**
     * @param ProductRequest $productRequest
     * @return bool
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function export(ProductRequest $productRequest): bool;
}
