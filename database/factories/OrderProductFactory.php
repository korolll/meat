<?php

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Services\Money\MoneyHelper;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(OrderProduct::class, function (Faker $faker) {
    return [
        'order_uuid' => function () {
            return factory(Order::class)->create()->uuid;
        },
        'product_uuid' => function (array $orderProduct) {
            $order = Order::findOrFail($orderProduct['order_uuid']);
            return factory(Product::class)->create([
                'user_uuid' => $order->store_user_uuid
            ])->uuid;
        },

        'quantity' => $faker->randomFloat(3, 1, 10),
        'price_with_discount' => $faker->randomFloat(2, 10, 1000),
        'discount' => $faker->randomFloat(2, 10, 1000),
        'total_discount' => function (array $orderProduct) use ($faker) {
            $value = MoneyHelper::of($orderProduct['quantity'])
                ->multipliedBy($orderProduct['discount']);
            return MoneyHelper::toFLoat($value);
        },
        'total_amount_with_discount' => function (array $orderProduct) use ($faker) {
            $value = MoneyHelper::of($orderProduct['quantity'])
                ->multipliedBy($orderProduct['price_with_discount']);
            return MoneyHelper::toFLoat($value);
        },
        'total_weight' => function (array $orderProduct) use ($faker) {
            /** @var Product $product */
            $product = Product::findOrFail($orderProduct['product_uuid']);
            return $orderProduct['quantity'] * $product->assortment->weight;
        }
    ];
});
