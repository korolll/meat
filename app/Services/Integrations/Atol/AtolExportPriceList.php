<?php

namespace App\Services\Integrations\Atol;

use App\Models\PriceList;
use App\Services\Integrations\Atol\Contracts\AtolExportPriceListContract;
use GuzzleHttp\Client;

class AtolExportPriceList implements AtolExportPriceListContract
{
    /**
     * Параметры файла экспорта
     */
    private const FILE_NAME = 'import';
    private const FILE_EXTENSION = 'txt';
    private const FILE_TYPE = 'frontol_spr';
    private const FILE_NAME_AND_EXTENSION = self::FILE_NAME . '.' . self::FILE_EXTENSION;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string|null
     */
    protected $uri;

    /**
     * @param Client $client
     * @param null|string $uri
     */
    public function __construct(Client $client, ?string $uri)
    {
        $this->client = $client;
        $this->uri = $uri;
    }

    /**
     * @param PriceList $priceList
     */
    public function export(PriceList $priceList): void
    {
        $this->client->post($this->uri, [
            'multipart' => [
                [
                    'name' => 'data',
                    'contents' => $this->makeData($priceList),
                    'filename' => self::FILE_NAME_AND_EXTENSION,
                ],
                [
                    'name' => 'meta',
                    'contents' => $this->makeMeta($priceList),
                ],
            ],
        ]);
    }

    /**
     * @param PriceList $priceList
     * @return string
     */
    private function makeData(PriceList $priceList): string
    {
        $transferFile = app(MakePriceListAtolTransferFile::class, compact('priceList'));

        return (string)$transferFile;
    }

    /**
     * @param PriceList $priceList
     * @return string
     */
    private function makeMeta(PriceList $priceList): string
    {
        $meta = [
            'filename' => self::FILE_NAME_AND_EXTENSION,
            'ext' => self::FILE_EXTENSION,
            'type' => self::FILE_TYPE,
            'address' => [
                'enterpriseId' => $priceList->user_uuid,
            ],
        ];

        return json_encode($meta);
    }
}
