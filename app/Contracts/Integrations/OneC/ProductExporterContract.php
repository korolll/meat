<?php

namespace App\Contracts\Integrations\OneC;

use App\Models\Product;

interface ProductExporterContract
{
    /**
     * @param Product $product
     * @return bool
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function export(Product $product): bool;
}
