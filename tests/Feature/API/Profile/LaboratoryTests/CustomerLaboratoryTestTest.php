<?php

namespace Tests\Feature\API\Profile\LaboratoryTests;

use App\Models\File;
use App\Models\FileCategory;
use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCaseNotificationsFake;

class CustomerLaboratoryTestTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    const SAVE_FIELD_LIST = [
        'laboratory_test_appeal_type_uuid',
        'laboratory_test_status_id',

        'customer_full_name',
        'customer_organization_name',
        'customer_organization_address',
        'customer_inn',
        'customer_kpp',
        'customer_ogrn',

        'customer_position',
        'customer_bank_correspondent_account',
        'customer_bank_current_account',
        'customer_bank_identification_code',
        'customer_bank_name',

        'batch_number',
        'parameters',

        'assortment_barcode',
        'assortment_uuid',
        'assortment_name',
        'assortment_manufacturer',
        'assortment_production_standard_id',
        'assortment_supplier_user_uuid',
    ];

    /**
     * @test
     */
    public function index()
    {
        $self = factory(User::class)->state('store')->create();
        $laboratoryTest = factory(LaboratoryTest::class)->state('new')->create([
            'customer_user_uuid' => $self->uuid
        ]);
        $json = $this->be($self)->getJson("/api/profile/laboratory-tests/customer");

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $laboratoryTest->uuid
                ]
            ],
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        $self = factory(User::class)->state('laboratory')->create();
        $laboratoryTest = factory(LaboratoryTest::class)->state('new')->create([
            'customer_user_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson("/api/profile/laboratory-tests/customer/{$laboratoryTest->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $laboratoryTest->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function storeCreated()
    {
        $self = factory(User::class)->state('store')->create();

        /** @var LaboratoryTest $laboratoryTest */
        $laboratoryTest = factory(LaboratoryTest::class)->make();
        $data = $laboratoryTest->only([
            'laboratory_test_status_id',
            'customer_full_name',
            'customer_organization_name',
        ]);

        $json = $this->be($self)->postJson('/api/profile/laboratory-tests/customer', $data);
        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('laboratory_tests', $data);
    }

    /**
     * @test
     */
    public function storeNew()
    {
        $self = factory(User::class)->state('store')->create();
        $file = factory(File::class)->create([
            'user_uuid' => $self->uuid,
            'file_category_id' => FileCategory::ID_LABORATORY_TEST_FILE_CUSTOMER,
        ]);

        /** @var LaboratoryTest $laboratoryTest */
        $laboratoryTest = factory(LaboratoryTest::class)->state('new')->make();
        $data = $laboratoryTest->only(static::SAVE_FIELD_LIST);
        $data['customer_files'] = [['uuid' => $file->uuid, 'public_name' => 'some public name']];

        $json = $this->be($self)->postJson('/api/profile/laboratory-tests/customer', $data);
        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('laboratory_tests', Arr::except($data, ['customer_files']));
    }

    /**
     * @test
     */
    public function update()
    {
        $self = factory(User::class)->state('store')->create();
        /** @var LaboratoryTest $laboratoryTest */
        $laboratoryTest = factory(LaboratoryTest::class)->create([
            'customer_user_uuid' => $self->uuid
        ]);

        $file = factory(File::class)->create([
            'user_uuid' => $self->uuid,
            'file_category_id' => FileCategory::ID_LABORATORY_TEST_FILE_CUSTOMER,
        ]);

        $laboratoryTestUpdate = factory(LaboratoryTest::class)->state('new')->make();
        $data = $laboratoryTestUpdate->only(static::SAVE_FIELD_LIST);
        $data['customer_files'] = [['uuid' => $file->uuid, 'public_name' => 'some public name']];

        $json = $this->be($self)->putJson('/api/profile/laboratory-tests/customer/' . $laboratoryTest->uuid, $data);
        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('laboratory_tests', Arr::except($data, ['customer_files']));
        $this->assertDatabaseHas('file_laboratory_test', [
            'file_uuid' => $file->uuid,
            'laboratory_test_uuid' => $laboratoryTest->uuid,
            'file_category_id' => FileCategory::ID_LABORATORY_TEST_FILE_CUSTOMER,
        ]);
    }

    /**
     * @test
     */
    public function setStatus()
    {
        $self = factory(User::class)->state('store')->create();
        $laboratoryTest = factory(LaboratoryTest::class)->state('new')->create([
            'customer_user_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->putJson("/api/profile/laboratory-tests/customer/{$laboratoryTest->uuid}/set-status", [
            'laboratory_test_status_id' => LaboratoryTestStatus::ID_CANCELED
        ]);
        $json->assertSuccessful();
    }
}
