<?php

namespace Tests\Unit\App\Services\Integrations\OneC;

use App\Contracts\Integrations\OneC\BarcodeFormatterContract;
use App\Models\Product;
use App\Services\Integrations\OneC\ProductExporter;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery as m;
use Tests\TestCaseNotificationsFake;

class ExportProductTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @param Product $product
     * @return array
     */
    protected function getProductData(Product $product): array
    {
        $assortment = $product->assortment;
        $productCatalog = $product->catalog;
        $assortmentCatalog = $assortment->catalog;
        $barcodes = $assortment->barcodes->pluck('barcode')->toArray();
        return [
            '@type' => 'ExportProduct',
            'Product' => [
                'Uuid' => $product->uuid,
                'IsActive' => $product->is_active,
                'Assortment' => [
                    'AssortmentUnitId' => $assortment->assortment_unit_id,
                    'Barcodes' => $barcodes,

                    'Catalog' => [
                        'Uuid' => $assortmentCatalog->uuid,
                        'Name' => $assortmentCatalog->name,
                    ],

                    'CountryId' => $assortment->country_id,
                    'CreatedAt' => $assortment->created_at,
                    'Description' => $assortment->description,
                    'GroupBarcode' => $assortment->group_barcode,
                    'Ingredients' => $assortment->ingredients,
                    'IsStorable' => $assortment->is_storable,
                    'Manufacturer' => $assortment->manufacturer,
                    'MinDeliveryTime' => $product->min_delivery_time,
                    'MinQuantumInOrder' => $product->min_quantum_in_order,
                    'Name' => $assortment->name,
                    'NdsPercent' => $assortment->nds_percent,
                    'OkpoCode' => $assortment->okpo_code,
                    'ProductionStandardId' => $assortment->production_standard_id,
                    'ProductionStandardNumber' => $assortment->production_standard_number,
                    'Quantum' => $product->quantum,
                    'ShelfLife' => $assortment->shelf_life,
                    'ShortName' => $assortment->short_name,
                    'TemperatureMax' => $assortment->temperature_max,
                    'TemperatureMin' => $assortment->temperature_min,
                    'UpdatedAt' => $assortment->updated_at,
                    'Uuid' => $assortment->uuid,
                    'Volume' => $product->volume,
                    'Weight' => $assortment->weight,
                ],

                'Catalog' => [
                    'Uuid' => $productCatalog->uuid,
                    'Name' => $productCatalog->name,
                ],

                'CreatedAt' => $product->created_at,
                'UpdatedAt' => $product->updated_at,
            ]
        ];
    }

    /**
     * @param string $url
     * @param string $tokenHeader
     * @param string $token
     * @param bool $success
     *
     * @test
     * @testWith ["", "", "", false]
     *           ["123", "321", "444", true]
     */
    public function export(string $url, string $tokenHeader, string $token, bool $success)
    {
        /** @var Product $product */
        $product = factory(Product::class)->make();

        $times = (int) $success;
        $client = m::mock(Client::class);
        $client->shouldReceive('post')
            ->times($times)
            ->with($url, [
                'headers' => [$tokenHeader => $token],
                'json' => $this->getProductData($product)
            ]);

//        $formatter = m::mock(BarcodeFormatterContract::class);
        $formatter = app(BarcodeFormatterContract::class);
        // @todo Разобраться почему с mock не работает
//        $barcodes = $product->assortment->barcodes->pluck('barcode')->toArray();
//        $formatter
//            ->shouldReceive('format')
//            ->times($times * count($barcodes) + 1)
//            ->withArgs(function ($arg) use ($product, $barcodes) {
//                return in_array($arg, [$product->assortment->group_barcode] + $barcodes);
//            })
//            ->andReturn(
//                $product->assortment->group_barcode,
//                ...$barcodes
//            );

        /** @var ProductExporter|m\Mock $object */
        $object = m::mock(ProductExporter::class, [$client, $url, $tokenHeader, $token, $formatter])->makePartial();
        $this->assertEquals($success, $object->export($product));
    }
}
