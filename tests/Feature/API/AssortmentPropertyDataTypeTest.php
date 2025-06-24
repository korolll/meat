<?php

namespace Tests\Feature\API;

use App\Models\AssortmentProperty;
use App\Models\AssortmentPropertyDataType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class AssortmentPropertyDataTypeTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $self = factory(User::class)->create();
        $json = $this->be($self)->getJson('/api/assortment-property-data-types');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'id' => AssortmentPropertyDataType::ID_STRING,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $typeId =  AssortmentPropertyDataType::ID_ENUM;

        $self = factory(User::class)->create();
        $json = $this->be($self)->getJson("/api/assortment-property-data-types/{$typeId}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'id' => $typeId,
            ],
        ]);
    }
}
