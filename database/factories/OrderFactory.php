<?php

use App\Models\Client;
use App\Models\ClientCreditCard;
use App\Models\Order;
use App\Models\OrderDeliveryType;
use App\Models\OrderPaymentType;
use App\Models\OrderStatus;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Order::class, function (Faker $faker) {
    return [
        'store_user_uuid' => function () {
            return factory(User::class)->state('store')->create()->uuid;
        },
        'client_uuid' => function () {
            return factory(Client::class)->create()->uuid;
        },
        'client_credit_card_uuid' => function ($attrs) {
            return ClientCreditCard::factory()->createOne([
                'client_uuid' => $attrs['client_uuid']
            ])->uuid;
        },
        'order_status_id' => OrderStatus::ID_NEW,
        'order_delivery_type_id' => OrderDeliveryType::ID_DELIVERY,
        'order_payment_type_id' => OrderPaymentType::ID_CASH,

        'client_comment' => null,
        'client_email' => $faker->email,
        'client_address_data' => null,

        'is_paid' => false,

        'delivery_price' => $faker->numberBetween(1, 1000),

        'total_discount_for_products' => 0,
        'total_price_for_products_with_discount' => 0,
        'total_price' => 0,
        'total_weight' => 0,
        'total_quantity' => 0,

        'planned_delivery_datetime_from' => $faker->dateTimeBetween('0 hours', '6 hours'),
        'planned_delivery_datetime_to' => $faker->dateTimeBetween('7 hours', '11 hours'),
    ];
});
