<?php

namespace Tests\Feature\API;

use App\Models\Assortment;
use App\Models\AssortmentProperty;
use App\Models\AssortmentPropertyDataType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCaseNotificationsFake;

class AssortmentPropertyTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $assortmentProperty = factory(AssortmentProperty::class)->state('searchable')->create();

        $self = factory(User::class)->create();
        $json = $this->be($self)->getJson('/api/assortment-properties?per_page=1000');

        $json->assertSuccessful()->assertJsonFragment(
            [
                'uuid' => $assortmentProperty->uuid,
                'name' => $assortmentProperty->name,
            ]
        );
    }

    /**
     * @test
     * @testWith [false]
     *           [true]
     *
     * @param $isSearchable boolean
     */
    public function indexFilterIsSearchable($isSearchable)
    {
        factory(AssortmentProperty::class)->create();
        $assortmentProperty = factory(AssortmentProperty::class)->state('searchable')->create();

        $self = factory(User::class)->create();
        if ($isSearchable) {
            $data = [
                'where' => [
                    ['is_searchable', '=', true],
                ]
            ];

            $dataResult = [
                'data' => [
                    [
                        'uuid' => $assortmentProperty->uuid,
                        'name' => $assortmentProperty->name,
                        'is_searchable' => true
                    ]
                ]
            ];
        } else {
            $data = [];
            $dataResult = [
                'data' => []
            ];
        }

        // Протестим еще и фильтрацию тегов
        $json = $this->be($self)->json('get', '/api/assortment-properties', $data);

        $json->assertSuccessful()->assertJson($dataResult);
    }

    /**
     * @test
     */
    public function store()
    {
        $assortmentProperty = factory(AssortmentProperty::class)->make([
            'assortment_property_data_type_id' => AssortmentPropertyDataType::ID_ENUM
        ]);

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/assortment-properties', [
            'name' => $assortmentProperty->name,
            'assortment_property_data_type_id' => $assortmentProperty->assortment_property_data_type_id,
            'is_searchable' => true
        ]);

        $data = [
            'uuid' => $json->json('data.uuid'),
            'name' => $assortmentProperty->name,
            'assortment_property_data_type_id' => $assortmentProperty->assortment_property_data_type_id,
            'is_searchable' => true
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('assortment_properties', $data);
    }

    /**
     * @test
     */
    public function storeWithNonUniqueName()
    {
        $assortmentProperty = factory(AssortmentProperty::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson('/api/assortment-properties', [
            'name' => $assortmentProperty->name,
        ]);

        $json->assertJsonValidationErrors('name');
    }

    /**
     * @test
     */
    public function show()
    {
        $assortmentProperty = factory(AssortmentProperty::class)->create();

        $self = factory(User::class)->create();
        $json = $this->be($self)->getJson("/api/assortment-properties/{$assortmentProperty->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $assortmentProperty->uuid,
                'name' => $assortmentProperty->name,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update()
    {
        $assortmentPropertyOld = factory(AssortmentProperty::class)->create();
        $assortmentPropertyNew = factory(AssortmentProperty::class)->make();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->putJson("/api/assortment-properties/{$assortmentPropertyOld->uuid}", [
            'name' => $assortmentPropertyNew->name,
        ]);

        $data = [
            'uuid' => $assortmentPropertyOld->uuid,
            'name' => $assortmentPropertyNew->name,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('assortment_properties', $data);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $assortmentProperty = factory(AssortmentProperty::class)->create();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/assortment-properties/{$assortmentProperty->uuid}");

        $data = [
            'uuid' => $assortmentProperty->uuid,
            'name' => $assortmentProperty->name,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     * @param string $userType
     * @param string $dataType
     * @param string $value
     * @param int $httpErrorCode
     *
     * @test
     * @testWith ["store", "enum", "test", 403]
     *           ["admin", "string", "test", 400]
     *           ["admin", "enum", "t", 422]
     *           ["admin", "enum", "test"]
     */
    public function addAvailableValue(string $userType, string $dataType, string $value, int $httpErrorCode = 0)
    {
        $assortmentProperty = factory(AssortmentProperty::class)->create([
            'assortment_property_data_type_id' => $dataType,
            'available_values' => []
        ]);

        $self = factory(User::class)->state($userType)->create();
        $json = $this->be($self)->postJson("/api/assortment-properties/{$assortmentProperty->uuid}/add-available-value", [
            'value' => $value
        ]);

        $data = [
            'uuid' => $assortmentProperty->uuid,
            'name' => $assortmentProperty->name,
            'available_values' => [$value],
        ];

        if ($httpErrorCode) {
            $json->assertStatus($httpErrorCode);

            return;
        }
        $json->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     * @param string $userType
     * @param string $dataType
     * @param string $value
     * @param int $httpErrorCode
     *
     * @test
     * @testWith ["store", "enum", "test", 403]
     *           ["admin", "string", "test", 400]
     *           ["admin", "number", "test", 400]
     *           ["admin", "enum", "x", 422]
     *           ["admin", "enum", "existValue", 422]
     *           ["admin", "enum", "test"]
     */
    public function removeAvailableValue(string $userType, string $dataType, string $value, int $httpErrorCode = 0)
    {
        $assortmentProperty = factory(AssortmentProperty::class)->create([
            'assortment_property_data_type_id' => $dataType,
            'available_values' => ['test', 'existValue']
        ]);

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();
        $assortment->assortmentProperties()->attach($assortmentProperty, [
            'value' => 'existValue'
        ]);

        $self = factory(User::class)->state($userType)->create();
        $json = $this->be($self)->postJson("/api/assortment-properties/{$assortmentProperty->uuid}/remove-available-value", [
            'value' => $value
        ]);

        $data = [
            'uuid' => $assortmentProperty->uuid,
            'name' => $assortmentProperty->name,
            'available_values' => ['existValue'],
        ];

        if ($httpErrorCode) {
            $json->assertStatus($httpErrorCode);

            return;
        }
        $json->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     * @param string $currentType
     * @param string $newType
     * @param array|null $available_values
     * @param bool $error
     *
     * @test
     * @dataProvider changeDataTypeDataProvider
     */
    public function changeDataType(string $currentType, string $newType, $available_values, bool $error = false)
    {
        $assortmentProperty = factory(AssortmentProperty::class)->create([
            'assortment_property_data_type_id' => $currentType,
            'available_values' => $available_values
        ]);

        if ($available_values) {
            foreach ($available_values as $value) {
                $assortment = factory(Assortment::class)->create();
                $assortment->assortmentProperties()->attach($assortmentProperty, ['value' => $value]);
            }
        } else {
            $assortment = factory(Assortment::class)->create();
            $assortment->assortmentProperties()->attach($assortmentProperty, ['value' => 'test']);
            $assortment = factory(Assortment::class)->create();
            $assortment->assortmentProperties()->attach($assortmentProperty, ['value' => 123]);
        }

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->postJson("/api/assortment-properties/{$assortmentProperty->uuid}/change-data-type", [
            'assortment_property_data_type_id' => $newType
        ]);

        $data = [
            'uuid' => $assortmentProperty->uuid,
            'name' => $assortmentProperty->name,
            'assortment_property_data_type_id' => $newType,
            'available_values' => $available_values,
        ];

        if ($error) {
            $json->assertStatus(Response::HTTP_BAD_REQUEST);

            return;
        }
        $json->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     * @return array
     */
    public function changeDataTypeDataProvider(): array
    {
        return [
            ["enum", "number", [], true],
            ["string", "number", [], true],
            ["number", "string", null, false],
            ["enum", "string", null, false],
            ["string", "enum", ["123", "test"]],
            ["number", "enum", ["123", "test"]]
        ];
    }
}
