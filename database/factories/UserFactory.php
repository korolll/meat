<?php

use App\Models\FileCategory;
use App\Models\LegalForm;
use App\Models\Region;
use App\Models\User;
use App\Models\File;
use App\Models\UserType;
use App\Models\UserVerifyStatus;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(User::class, function (Faker $faker) {
    return [
        'user_type_id' => $faker->randomElement(UserType::USER_AVAILABLE_IDS),
        'full_name' => $faker->name,
        'legal_form_id' => $faker->randomElement(LegalForm::USER_AVAILABLE_IDS),
        'organization_name' => $faker->company,
        'organization_address' => $faker->address,
        'address' => $faker->address,
        'email' => $faker->unique()->email,
        'phone' => $faker->numerify('+79#########'),
        'password' => $faker->password,
        'inn' => $faker->numerify('############'),
        'kpp' => function (array $user) use ($faker) {
            return $user['legal_form_id'] !== LegalForm::ID_IP ? $faker->numerify('#########') : null;
        },
        'ogrn' => $faker->numerify('###############'),
        'region_uuid' => function () {
            return factory(Region::class)->create()->uuid;
        },
        'address_latitude' => $faker->randomFloat(8, -90, 90),
        'address_longitude' => $faker->randomFloat(8, -180, 180),
        'work_hours_from' => $faker->dateTimeBetween('today', 'today +12 hour')->format('H:i'),
        'work_hours_till' => $faker->dateTimeBetween('today +12 hour', 'tomorrow')->format('H:i'),
        'brand_name' => $faker->company,
        'user_verify_status_id' => UserVerifyStatus::ID_APPROVED,
        'is_email_verified' => true,

        'position' => $faker->sentence(3),
        'bank_correspondent_account' => $faker->numerify('####################'),
        'bank_current_account' => $faker->numerify('####################'),
        'bank_identification_code' => $faker->numerify('#########'),
        'bank_name' => $faker->company,

        'signer_type_id' => \App\Models\SignerType::ID_CONFIDANT,
        'signer_full_name' => $faker->name,
        'power_of_attorney_number' => $faker->numerify('############'),
        'date_of_power_of_attorney' => $faker->dateTimeBetween(),
        'ip_registration_certificate_number' => $faker->numerify('############'),
        'date_of_ip_registration_certificate' => $faker->dateTimeBetween(),

        'has_parking' => $faker->boolean,
        'has_ready_meals' => $faker->boolean,
        'has_atms' => $faker->boolean,
    ];
});

$factory->state(User::class, 'admin', [
    'user_type_id' => UserType::ID_ADMIN,
]);

$factory->state(User::class, 'supplier', [
    'user_type_id' => UserType::ID_SUPPLIER,
]);

$factory->state(User::class, 'distribution-center', [
    'user_type_id' => UserType::ID_DISTRIBUTION_CENTER,
]);

$factory->state(User::class, 'store', [
    'user_type_id' => UserType::ID_STORE,
]);

$factory->state(User::class, 'delivery-service', [
    'user_type_id' => UserType::ID_DELIVERY_SERVICE,
]);

$factory->state(User::class, 'laboratory', [
    'user_type_id' => UserType::ID_LABORATORY,
]);

$factory->afterCreatingState(User::class, 'has-image', function (User $user) {
    $file = factory(File::class)->create([
        'file_category_id' => FileCategory::ID_SHOP_IMAGE,
        'user_uuid' => $user->uuid
    ]);

    $user->image()->associate($file)->save();
});
