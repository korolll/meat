<?php

namespace App\Services\Management\Client\Order\Payment;

use App\Models\AssortmentUnit;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Services\Money\MoneyHelper;
use App\Services\Quantity\FloatHelper;

class PaymentOrderBundleGenerator implements PaymentOrderBundleGeneratorInterface
{
    const DELIVERY_ID = '0000000000';

    /**
     * @param \App\Models\Client $client
     * @param \App\Models\Order  $order
     *
     * @return array
     */
    public function generate(Client $client, Order $order): array
    {
        /**
         * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:ws:requests:register_cart#orderbundle
         */
        $customerDetails = [
            'phone' => $client->phone
        ];
        if ($client->email) {
            $customerDetails['email'] = $client->email;
        }

        $cartItems = [];
        $orderProducts = $order->orderProducts;
        $orderProducts->loadMissing('product.assortment');

        /** @var \App\Models\OrderProduct $orderProduct */
        foreach ($orderProducts as $key => $orderProduct) {
            $product = $orderProduct->product;
            $assortment = $product->assortment;

            if (FloatHelper::isEqual($orderProduct->quantity, 0)) {
                continue;
            }

//            if ($assortment->assortment_unit_id === AssortmentUnit::ID_KILOGRAM) {
//                $denominator = 1000;
//                $quantity = FloatHelper::round($orderProduct->quantity);
//                $quantity = (int)($quantity * $denominator);
//            } else {
//                $denominator = 1;
//                $quantity = (int)$orderProduct->quantity;
//            }

            $cartItems[] = [
                'positionId' => $key + 1,
                'name' => $assortment->name,

                /** @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:ws:requests:register_cart#quantity */
                'quantity' => [
                    'value' => $orderProduct->quantity,
                    'measure' => $assortment->assortment_unit_id === AssortmentUnit::ID_KILOGRAM ? 'кг.' : 'шт.',
                ],
                'itemCode' => $assortment->uuid,

                /** @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:ws:requests:register_cart#tax */
                'tax' => [
                    'taxType' => 0,
                    'taxSum' => 0
                ],
                'itemPrice' => $this->calculateItemPrice($orderProduct),

                /** @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:ws:requests:register_cart#itemattributes */
                'itemAttributes' => [
                    'attributes' => [
                        ['name' => 'paymentMethod', 'value' => 1],
                        ['name' => 'paymentObject', 'value' => 1],
//                        ['name' => 'nomenclature', 'value' => str_replace('-', '', $assortment->uuid)],
//                        ['name' => 'markQuantity.numerator', 'value' => $quantity],
//                        ['name' => 'markQuantity.denominator', 'value' => $denominator],
                    ]
                ]
            ];
        }

        if ($order->delivery_price) {
            // Add delivery as service
            $cartItems[] = [
                'positionId' => $orderProducts->count() + 1,
                'name' => 'Доставка',
                'quantity' => [
                    'value' => 1,
                    'measure' => 'шт.'
                ],
                'itemCode' => static::DELIVERY_ID,
                'tax' => [
                    'taxType' => 0,
                    'taxSum' => 0
                ],
                'itemPrice' => MoneyHelper::toKopek($order->delivery_price),
                'itemAttributes' => [
                    'attributes' => [
                        ['name' => 'paymentMethod', 'value' => 1],
                        ['name' => 'paymentObject', 'value' => 1],
//                        ['name' => 'nomenclature', 'value' => static::DELIVERY_ID],
//                        ['name' => 'markQuantity.numerator', 'value' => 1],
//                        ['name' => 'markQuantity.denominator', 'value' => 1],
                    ]
                ]
            ];
        }

        return [
            'orderCreationDate' => $order->created_at->format('Y-m-d\TH:i:s'),
            'customerDetails' => $customerDetails,
            'cartItems' => [
                'items' => $cartItems
            ]
        ];
    }

    /**
     * @param \App\Models\OrderProduct $orderProduct
     *
     * @return int
     */
    protected function calculateItemPrice(OrderProduct $orderProduct): int
    {
        if (! $orderProduct->paid_bonus) {
            return MoneyHelper::toKopek($orderProduct->price_with_discount);
        }

        $price = MoneyHelper::of($orderProduct->total_amount_with_discount)
            ->dividedBy($orderProduct->quantity);

        return MoneyHelper::toKopek($price);
    }
}
