<?php

namespace App\Services\Integrations\OneC;

use App\Contracts\Integrations\OneC\BarcodeFormatterContract;

class BarcodeFormatter implements BarcodeFormatterContract
{
    /**
     * @param null|string $barcode
     * @return null|string
     */
    public function format(?string $barcode): ?string
    {
        if ($barcode === null) {
            return $barcode;
        }

        return preg_replace('/^0+(\d+)$/', '$1', $barcode);
    }

    /**
     * @param null|array $barcodes
     * @return null|array
     */
    public function formatArray(?array $barcodes): ?array
    {
        return array_map(function ($barcode) {
            return $this->format($barcode);
        }, $barcodes);
    }
}
