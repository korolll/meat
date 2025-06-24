<?php

namespace App\Contracts\Integrations\OneC;

interface BarcodeFormatterContract
{
    /**
     * @param null|string $barcode
     * @return null|string
     */
    public function format(?string $barcode): ?string;

    /**
     * @param null|array $barcodes
     * @return null|array
     */
    public function formatArray(?array $barcodes): ?array;
}
