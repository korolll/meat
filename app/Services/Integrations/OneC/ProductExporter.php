<?php

namespace App\Services\Integrations\OneC;

use App\Contracts\Integrations\OneC\BarcodeFormatterContract;
use App\Contracts\Integrations\OneC\ProductExporterContract;
use App\Models\Product;
use GuzzleHttp\Client;

/**
 * Class AssortmentExporter
 * @package App\Services\Integrations\OneC
 */
class ProductExporter implements ProductExporterContract
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
     * ProductExporter constructor.
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
     * @param Product $product
     * @return bool
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function export(Product $product): bool
    {
        if (empty($this->uri)) {
            return false;
        }

        $this->client->post($this->uri, [
            'headers' => $this->prepareHeaders(),
            'json' => $this->prepareJson($product),
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
     * @param Product $product
     * @return array
     */
    protected function prepareJson(Product $product): array
    {
        $product->loadMissing([
            'catalog',
            'assortment',
            'assortment.catalog',
        ]);

        $assortment = $product->assortment;
        $barcodes = $assortment->barcodes->pluck('barcode')->toArray();

        $productCatalog = $product->catalog;
        $assortmentCatalog = $assortment->catalog;

        return [
            '@type' => 'ExportProduct',
            'Product' => [
                'Uuid' => $product->uuid,
                'IsActive' => $product->is_active,
                'Assortment' => [
                    'AssortmentUnitId' => $assortment->assortment_unit_id,
                    'Barcodes' => $this->barcodeFormatter->formatArray($barcodes),

                    'Catalog' => [
                        'Uuid' => $assortmentCatalog->uuid,
                        'Name' => $assortmentCatalog->name,
                    ],

                    'CountryId' => $assortment->country_id,
                    'CreatedAt' => $assortment->created_at,
                    'Description' => $assortment->description,
                    'GroupBarcode' => $this->barcodeFormatter->format($assortment->group_barcode),
                    'Ingredients' => $assortment->ingredients,
                    'IsStorable' => $assortment->is_storable,
                    'Manufacturer' => $assortment->manufacturer,
                    // todo по хорошему это надо вынести на уровень выше, из асортимента в продукт
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
}
