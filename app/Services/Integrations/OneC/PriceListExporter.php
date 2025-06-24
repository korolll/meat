<?php

namespace App\Services\Integrations\OneC;

use App\Contracts\Integrations\OneC\BarcodeFormatterContract;
use App\Contracts\Integrations\OneC\PriceListExporterContract;
use App\Models\PriceList;
use App\Models\Product;
use GuzzleHttp\Client;

class PriceListExporter implements PriceListExporterContract
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string|null
     */
    protected $uri;

    /**
     * @var string|null
     */
    protected $tokenHeader;

    /**
     * @var string|null
     */
    protected $token;

    /**
     * @var BarcodeFormatterContract
     */
    protected $barcodeFormatter;

    /**
     * PriceListExporter constructor.
     * @param Client $client
     * @param null|string $uri
     * @param null|string $tokenHeader
     * @param null|string $token
     * @param BarcodeFormatterContract $barcodeFormatter
     */
    public function __construct(Client $client, ?string $uri, ?string $tokenHeader, ?string $token, BarcodeFormatterContract $barcodeFormatter)
    {
        $this->client = $client;
        $this->uri = $uri;
        $this->tokenHeader = $tokenHeader;
        $this->token = $token;
        $this->barcodeFormatter = $barcodeFormatter;
    }

    /**
     * @param PriceList $priceList
     * @return bool
     */
    public function export(PriceList $priceList): bool
    {
        if (empty($this->uri)) {
            return false;
        }

        $this->client->post($this->uri, [
            'headers' => $this->prepareHeaders(),
            'json' => $this->prepareJson($priceList),
        ]);

        return true;
    }

    /**
     * @return array
     */
    protected function prepareHeaders(): array
    {
        $headers = [];

        if ($this->tokenHeader) {
            $headers[$this->tokenHeader] = $this->token;
        }

        return $headers;
    }

    /**
     * @param PriceList $priceList
     * @return array
     */
    protected function prepareJson(PriceList $priceList): array
    {
        $priceList->loadMissing([
            'activeProducts.assortment.barcodes'
        ]);

        return [
            '@type' => 'ExportPriceList',
            'PriceListUuid' => $priceList->uuid,
            'UserUuid' => $priceList->user_uuid,
            'CustomerUserUuid' => $priceList->customer_user_uuid,
            'CustomerInn' => optional($priceList->customerUser)->inn,
            'DateFrom' => $priceList->date_from,
            'DateTill' => $priceList->date_till,
            'data' => $priceList->activeProducts->map(function (Product $product) {
                $barcode = $product->assortment->barcodes->first()->barcode;
                return [
                    'Barcode' => $this->barcodeFormatter->format($barcode),
                    'PriceNew' => $product->pivot->price_new,
                ];
            })->all(),
        ];
    }
}
