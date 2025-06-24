<?php

namespace App\Services\Integrations\OneC;

use App\Contracts\Integrations\OneC\CatalogExporterContract;
use App\Models\Catalog;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class CatalogExporter implements CatalogExporterContract
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
     * @param Client $client
     * @param string|null $uri
     * @param string|null $tokenHeader
     * @param string|null $token
     */
    public function __construct(Client $client, ?string $uri, ?string $tokenHeader, ?string $token)
    {
        $this->client = $client;
        $this->uri = $uri;
        $this->tokenHeader = $tokenHeader;
        $this->token = $token;
    }

    /**
     * @param Collection&Catalog[] $catalogs
     * @return bool
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function export(Collection $catalogs): bool
    {
        if (empty($this->uri)) {
            return false;
        }

        $this->client->post($this->uri, [
            'headers' => $this->prepareHeaders(),
            'json' => $this->prepareJson($catalogs),
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
     * @param Collection&Catalog[] $catalogs
     * @return array
     */
    protected function prepareJson(Collection $catalogs): array
    {
        return [
            '@type' => 'ExportCatalogs',
            'data' => $catalogs->map(function ($catalog) {
                return [
                    'Uuid' => $catalog->uuid,
                    'CatalogUuid' => $catalog->catalog_uuid,
                    'UserUuid' => $catalog->user_uuid,
                    'Name' => $catalog->name,
                    'Level' => $catalog->level,
                    'AssortmentsCount' => $catalog->assortments_count,
                    'CreatedAt' => $catalog->created_at,
                    'UpdatedAt' => $catalog->updated_at,
                ];
            })->all()
        ];
    }
}
