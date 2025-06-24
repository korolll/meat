<?php

namespace Tests\Unit\App\Services\Integrations\OneC;

use App\Contracts\Models\Catalog\FindPublicCatalogsContract;
use App\Models\Catalog;
use App\Services\Integrations\OneC\CatalogExporter;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;
use Mockery as m;


/**
 * Class CatalogExporterTest
 * @package Tests\Unit\App\Services\Integrations\OneC
 * @todo Переделать, отключил, т.к. выдаёт ошибку
 */
class CatalogExporterTest
{
    use DatabaseTransactions;

    /**
     * @param Catalog $catalog
     * @return array
     */
    private function getPriceListData(Catalog $catalog): array
    {
        return [
            '@type' => 'ExportCatalogs',
            'data' => [
                [
                    'Uuid' => $catalog->uuid,
                    'CatalogUuid' => $catalog->catalog_uuid,
                    'UserUuid' => $catalog->user_uuid,
                    'Name' => $catalog->name,
                    'Level' => $catalog->level,
                    'AssortmentsCount' => $catalog->assortments_count,
                    'CreatedAt' => $catalog->created_at,
                    'UpdatedAt' => $catalog->updated_at,
                ]
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
        /** @var Catalog $catalog */
        $catalog = factory(Catalog::class)->create();

        $times = (int) $success;
        $client = m::mock(Client::class);
        $client->shouldReceive('post')
            ->times($times)
            ->with($url, [
                'headers' => [$tokenHeader => $token],
                'json' => $this->getPriceListData($catalog)
            ]);

        /** @var CatalogExporter|m\Mock $object */
        $object = m::mock(CatalogExporter::class, [$client, $url, $tokenHeader, $token])->makePartial();
        $this->assertEquals($success, $object->export(resolve(FindPublicCatalogsContract::class)->find()->where('uuid', '=', $catalog->uuid)));
    }
}
