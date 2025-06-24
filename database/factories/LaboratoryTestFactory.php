<?php

use App\Models\Assortment;
use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestAppealType;
use App\Models\LaboratoryTestStatus;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(LaboratoryTest::class, function (Faker $faker) {
    $customer = factory(User::class)->state('distribution-center')->create();
    $assortment = factory(Assortment::class)->create();

    return [
        'laboratory_test_status_id' => LaboratoryTestStatus::ID_CREATED,
        'laboratory_test_appeal_type_uuid' => factory(LaboratoryTestAppealType::class)->create()->uuid,
        'customer_user_uuid' => $customer->uuid,
        'executor_user_uuid' => factory(User::class)->state('laboratory')->create()->uuid,

        'customer_full_name' => $customer->full_name,
        'customer_organization_name' => $customer->organization_name,
        'customer_organization_address' => $customer->organization_address,
        'customer_inn' => $customer->inn,
        'customer_kpp' => $customer->kpp,
        'customer_ogrn' => $customer->ogrn,

        'customer_position' => $customer->position,
        'customer_bank_correspondent_account' => $customer->bank_correspondent_account,
        'customer_bank_current_account' => $customer->bank_current_account,
        'customer_bank_identification_code' => $customer->bank_identification_code,
        'customer_bank_name' => $customer->bank_name,

        'batch_number' => $faker->sentence(1),
        'parameters' => $faker->sentence(3),

        'assortment_barcode' => $assortment->barcodes[0]->barcode,
        'assortment_uuid' => $assortment->uuid,
        'assortment_name' => $assortment->name,
        'assortment_manufacturer' => $assortment->manufacturer,
        'assortment_production_standard_id' => $assortment->production_standard_id,
        'assortment_supplier_user_uuid' => factory(User::class)->create()->uuid,
    ];
});

$factory->state(LaboratoryTest::class, 'new', [
    'laboratory_test_status_id' => LaboratoryTestStatus::ID_NEW,
]);

$factory->state(LaboratoryTest::class, 'in-work', [
    'laboratory_test_status_id' => LaboratoryTestStatus::ID_IN_WORK,
]);
