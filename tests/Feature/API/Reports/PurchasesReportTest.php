<?php

namespace Tests\Feature\API\Reports;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Promo\PromoDescriptionFirstOrder;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Models\PromoYellowPrice;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Tests\TestCaseNotificationsFake;

class PurchasesReportTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testReport()
    {
        /** @var User $user */
        $user = factory(User::class)->state('store')->create();
        $createdAt = Date::createFromDate(2019,1,2)->setTime(0,0,0);
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->create([
            'user_uuid' => $user->uuid,
            'created_at' => $createdAt,
            'total' => 10,
        ]);
        $receipt->refresh();
        /** @var ReceiptLine $receiptLine */
        $receiptLine = factory(ReceiptLine::class)->create([
            'receipt_uuid' => $receipt->uuid,
            'total' => 10,
            'quantity' => 2,
            'discount' => null,
        ]);

        /** @var Order $order1 */
        $order1 = factory(Order::class)->create([
            'store_user_uuid' => $user->uuid,
            'planned_delivery_datetime_from' => $createdAt,
            'order_status_id' => OrderStatus::ID_DONE
        ]);
        /** @var OrderProduct $orderProduct1 */
        $orderProduct1 = factory(OrderProduct::class)->create([
            'order_uuid' => $order1->uuid,
            'total_amount_with_discount' => 20,
            'quantity' => 5
        ]);

        /** @var Order $order2 */
        $order2 = factory(Order::class)->create([
            'store_user_uuid' => $user->uuid,
            'planned_delivery_datetime_from' => $createdAt,
            'order_status_id' => OrderStatus::ID_DONE
        ]);
        /** @var OrderProduct $orderProduct2 */
        $orderProduct2 = factory(OrderProduct::class)->create([
            'order_uuid' => $order2->uuid,
            'product_uuid' => $receiptLine->product_uuid,
            'total_amount_with_discount' => 30,
            'quantity' => 10
        ]);

        $data = [
            'date_start' => Date::createFromDate(2019,1,1),
            'date_end' => Date::createFromDate(2019,1,10),
        ];

        $json = $this->be($user)->json('get', '/api/reports/purchases-report', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'total_quantity' => 12,
                    'total_sum' => 40,
                    'assortment' => [
                        'uuid' => $orderProduct2->product->assortment_uuid
                    ]
                ],
                [
                    'total_quantity' => 5,
                    'total_sum' => 20,
                    'assortment' => [
                        'uuid' => $orderProduct1->product->assortment_uuid
                    ]
                ],
            ],
        ]);
    }

    /**
     *
     */
    public function testActionsReport()
    {
        /** @var User $user */
        $user = factory(User::class)->state('store')->create();
        $createdAt = Date::createFromDate(2019,1,2)->setTime(0,0,0);
        /** @var Receipt $receipt */
        $receipt = factory(Receipt::class)->create([
            'user_uuid' => $user->uuid,
            'created_at' => $createdAt,
            'total' => 10,
        ]);
        $receipt->refresh();
        /** @var ReceiptLine $receiptLine */
        $receiptLine = factory(ReceiptLine::class)->create([
            'receipt_uuid' => $receipt->uuid,
            'total' => 10,
            'quantity' => 2,
            'discount' => null,
            'discountable_type' => PromoYellowPrice::class,
        ]);

        /** @var Order $order1 */
        $order1 = factory(Order::class)->create([
            'store_user_uuid' => $user->uuid,
            'planned_delivery_datetime_from' => $createdAt,
            'order_status_id' => OrderStatus::ID_DONE
        ]);
        /** @var OrderProduct $orderProduct1 */
        $orderProduct1 = factory(OrderProduct::class)->create([
            'order_uuid' => $order1->uuid,
            'total_amount_with_discount' => 20,
            'quantity' => 5,
            'discountable_type' => PromoDiverseFoodClientDiscount::class,
        ]);

        /** @var Order $order2 */
        $order2 = factory(Order::class)->create([
            'store_user_uuid' => $user->uuid,
            'planned_delivery_datetime_from' => $createdAt,
            'order_status_id' => OrderStatus::ID_DONE
        ]);
        /** @var OrderProduct $orderProduct2 */
        $orderProduct2 = factory(OrderProduct::class)->create([
            'order_uuid' => $order2->uuid,
            'product_uuid' => $receiptLine->product_uuid,
            'total_amount_with_discount' => 30,
            'quantity' => 10,
            'discountable_type' => PromoYellowPrice::class,
        ]);

        /** @var Order $order3 */
        $order3 = factory(Order::class)->create([
            'store_user_uuid' => $user->uuid,
            'planned_delivery_datetime_from' => $createdAt,
            'order_status_id' => OrderStatus::ID_DONE
        ]);
        /** @var OrderProduct $orderProduct2 */
        $orderProduct3 = factory(OrderProduct::class)->create([
            'order_uuid' => $order3->uuid,
            'product_uuid' => $receiptLine->product_uuid,
            'total_amount_with_discount' => 30,
            'quantity' => 10,
            'discountable_type' => PromoDescriptionFirstOrder::class,
        ]);

        $data = [
            'date_start' => Date::createFromDate(2019,1,1),
            'date_end' => Date::createFromDate(2019,1,10),
        ];

        $json = $this->be($user)->json('get', '/api/reports/purchases-actions-report', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'discountable_type' => PromoDiverseFoodClientDiscount::class,
                    'total_quantity' => 5,
                    'total_sum' => 20,
                ],
                [
                    'discountable_type' => PromoDescriptionFirstOrder::class,
                    'total_quantity' => 10,
                    'total_sum' => 30,
                ],
                [
                    'discountable_type' => PromoYellowPrice::class,
                    'total_quantity' => 12,
                    'total_sum' => 40,
                ],
            ],
        ]);
    }
}
