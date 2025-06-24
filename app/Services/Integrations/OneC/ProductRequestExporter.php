<?php

namespace App\Services\Integrations\OneC;

use App\Contracts\Integrations\OneC\BarcodeFormatterContract;
use App\Models\Product;
use App\Models\ProductRequest;
use GuzzleHttp\Client;

class ProductRequestExporter implements ProductRequestExporterContract
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
     * ProductRequestExporter constructor.
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
     * @param ProductRequest $productRequest
     * @return bool
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function export(ProductRequest $productRequest): bool
    {
        if (empty($this->uri)) {
            return false;
        }

        $this->client->post($this->uri, [
            'headers' => $this->prepareHeaders(),
            'json' => $this->prepareJson($productRequest),
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
     * @param ProductRequest $productRequest
     * @return array
     */
    protected function prepareJson(ProductRequest $productRequest): array
    {
        $productRequest->loadMissing([
            'products.assortment',
        ]);

        return [
            '@type' => 'ExportProductRequest',
            'ProductRequestUuid' => $productRequest->uuid,
            'CreateDate' => $productRequest->created_at,
            'ExpectedDeliveryDate' => $productRequest->expected_delivery_date,
            'CustomerUserInn' => $productRequest->customerUser->inn,
            'CustomerAddress' => $productRequest->customerUser->address,
            'SupplierUserInn' => $productRequest->supplierUser->inn,
            'SupplierAddress' => $productRequest->supplierUser->address,
            'ConfirmedDate' => $productRequest->confirmed_date,
            'data' => $productRequest->products->map(function (Product $product) {
                $barcode = $product->assortment->barcodes->first()->barcode;
                return [
                    'Barcode' => $this->barcodeFormatter->format($barcode),
                    'Quantity' => $product->pivot->quantity,
                    'ProductUuid' => $product->uuid,
                    'AssortmentUuid' => $product->assortment_uuid
                ];
            })->all(),
        ];
    }
}
