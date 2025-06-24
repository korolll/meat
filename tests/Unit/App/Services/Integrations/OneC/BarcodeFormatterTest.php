<?php

namespace Tests\Unit\App\Services\Integrations\OneC;

use App\Services\Integrations\OneC\BarcodeFormatter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery as m;
use Tests\TestCaseNotificationsFake;

class BarcodeFormatterTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @param mixed $barcode
     * @param array $barcodes
     * @param string $formattedBarcode
     *
     * @test
     * @testWith [123, [123,123], "123"]
     *           ["000123", ["000123","000123"], "123"]
     *           ["1230000000", ["1230000000","1230000000"], "1230000000"]
     *           ["0000000123000123", ["0000000123000123","0000000123000123"], "123000123"]
     *           ["0123210004", ["0123210004","0123210004"], "123210004"]
     *           [null, [null,null], null]
     */
    public function format($barcode, $barcodes, ?string $formattedBarcode)
    {
        /** @var BarcodeFormatter|m\Mock $object */
        $object = m::mock(BarcodeFormatter::class)->makePartial();
        $this->assertEquals($formattedBarcode, $object->format($barcode));

        foreach ($object->formatArray($barcodes) as $item) {
            $this->assertEquals($formattedBarcode, $item);
        }
    }
}
