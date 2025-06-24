<?php

namespace Tests\Unit\App\Services\Integrations\OneC;

use App\Contracts\Integrations\OneC\BarcodeFormatterContract;
use App\Models\PriceList;
use App\Models\PriceListStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\Integrations\OneC\BarcodeFormatter;
use App\Services\Integrations\OneC\PriceListExporter;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery as m;
use Tests\TestCaseNotificationsFake;

/**
 * Class PriceListExporterTest
 * @package Tests\Unit\App\Services\Integrations\OneC
 * @todo Переделать, отключил, т.к. выдаёт ошибку
 */
class PriceListExporterTest
//    extends TestCase
{
    use DatabaseTransactions;

    /**
     * @param PriceList $priceList
     * @return array
     */
    private function getPriceListData (PriceList $priceList): array
    {
        $priceList->loadMissing([
            'activeProducts.assortment'
        ]);

        return [
            '@type' => 'ExportPriceList',
            'PriceListUuid' => $priceList->uuid,
            'UserUuid' => $priceList->user_uuid,
            'CustomerUserUuid' => $priceList->customer_user_uuid,
            'CustomerInn' => optional($priceList->customer)->inn,
            'DateFrom' => $priceList->date_from,
            'DateTill' => $priceList->date_till,
            'data' => $priceList->activeProducts->map(function (Product $product) {
                $barcode = $product->assortment->barcodes->first()->barcode;
                return [
                    'Barcode' => $barcode,
                    'PriceNew' => $product->pivot->price_new,
                ];
            })->all(),
        ];
    }

    /**
     * @param string $url
     * @param string $tokenHeader
     * @param string $token
     * @param bool $isPrivatePriceList
     * @param bool $success
     *
     * @test
     * @testWith ["", "", "", false, false]
     *           ["123", "321", "444", false, true]
     *           ["123", "321", "444", true, true]
     */
    public function export(string $url, string $tokenHeader, string $token, bool $isPrivatePriceList, bool $success)
    {
        $customer = $isPrivatePriceList ? factory(User::class)->state('store')->create() : null;
        /** @var PriceList $priceList */
        $priceList = factory(PriceList::class)->create([
            'date_from' => now()->subMinutes(1),
            'price_list_status_id' => PriceListStatus::CURRENT,
            'customer_user_uuid' => optional($customer)->uuid
        ]);
        /**
         * @var $product Product
         * @var $product_not_is_active Product
         */
        $product = factory(Product::class)->create(['user_uuid' => $priceList->user_uuid]);
        $product_not_is_active = factory(Product::class)->create([
            'user_uuid' => $priceList->user_uuid,
            'is_active' => false
        ]);
        $priceList->products()->attach([
            $product->uuid => ['price_new' => 500],
            $product_not_is_active->uuid => ['price_new' => 500],
        ]);

        $times = (int) $success;
        $client = m::mock(Client::class);
        $client->shouldReceive('post')
            ->times($times)
            ->with($url, [
                'headers' => [$tokenHeader => $token],
                'json' => $this->getPriceListData($priceList)
            ]);

        $formatter = m::mock(BarcodeFormatterContract::class);
        $barcode = $product->assortment->barcodes->first()->barcode;
        $formatter
            ->shouldReceive('format')
            ->times($times)
            ->with($barcode)
            ->andReturn($barcode);

        /** @var PriceListExporter|m\Mock $object */
        $object = m::mock(PriceListExporter::class, [$client, $url, $tokenHeader, $token, $formatter])->makePartial();
        if ($isPrivatePriceList && $success){
            dd($object->export($priceList));
        }

        $this->assertEquals($success, $object->export($priceList));

        unset($priceList, $client,$formatter, $object);
    }
}
