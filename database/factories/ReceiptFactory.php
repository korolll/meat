<?php

use App\Models\LoyaltyCard;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Receipt::class, function (Faker $faker) {
    $loyaltyCard = factory(LoyaltyCard::class)->state('owned')->create();

    return [
        'user_uuid' => function () {
            return factory(User::class)->state('store')->create()->uuid;
        },
        'receipt_package_id' => $faker->numberBetween(10000, 20000),
        'id' => $faker->numberBetween(1000, 2000),
        'loyalty_card_uuid' => $loyaltyCard->uuid,
        'loyalty_card_type_uuid' => $loyaltyCard->loyalty_card_type_uuid,
        'loyalty_card_number' => $loyaltyCard->number,
        'total' => $faker->numberBetween(1000, 2000),
        'refund_by_receipt_uuid' => null,
        'created_at' => now(),
    ];
});

$factory->afterCreating(Receipt::class, function (Receipt $receipt) {
    $receipt->user->loyaltyCardTypes()->attach($receipt->loyaltyCardType);
});

$factory->afterCreatingState(Receipt::class, 'has-lines', function (Receipt $receipt) {
    $receipt->receiptLines()->save(
        factory(ReceiptLine::class)->make()
    );
});
