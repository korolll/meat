<?php

namespace App\Listeners;

use App\Events\OrderIsDone;
use App\Events\ReceiptReceived;
use App\Models\PromotionInTheShopLastPurchase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Date;
use Ramsey\Uuid\Uuid;

class UpdatePromoInTheShopListener implements ShouldQueue
{
    /**
     * @param $event
     *
     * @throws \Throwable
     */
    public function handle($event)
    {
        $class = get_class($event);
        switch ($class) {
            case ReceiptReceived::class:
                $this->handleReceipt($event);
                break;
            case OrderIsDone::class:
                $this->handleOrderStatus($event);
                break;
            default:
                throw new \Exception('Bad provided event: ' . $class);
        }
    }

    /**
     * @param \App\Events\ReceiptReceived $event
     *
     * @throws \Throwable
     */
    protected function handleReceipt(ReceiptReceived $event): void
    {
        $receipt = $event->receipt;
        $card = $receipt->loyaltyCard;
        if (! $card) {
            return;
        }

        if (! $card->client_uuid) {
            return;
        }

        $catalogUuids = $receipt->receiptLines()
            ->join('assortments', 'assortments.uuid', 'receipt_lines.assortment_uuid')
            ->distinct()
            ->toBase()
            ->get('assortments.catalog_uuid')
            ->pluck('catalog_uuid');
        $this->updatePurchaseHistory($catalogUuids, $card->client_uuid);
    }

    /**
     * @param \App\Events\OrderIsDone $event
     */
    protected function handleOrderStatus(OrderIsDone $event): void
    {
        $order = $event->order;
        $catalogUuids = $order->orderProducts()
            ->join('products', 'products.uuid', 'order_products.product_uuid')
            ->join('assortments', 'assortments.uuid', 'products.assortment_uuid')
            ->distinct()
            ->toBase()
            ->get('assortments.catalog_uuid')
            ->pluck('catalog_uuid');

        $this->updatePurchaseHistory($catalogUuids, $order->client_uuid);
    }

    /**
     * @param iterable $catalogUuids
     * @param string   $clientUuid
     */
    protected function updatePurchaseHistory(iterable $catalogUuids, string $clientUuid): void
    {
        $values = [];
        $deleteAfterDays = config('app.promotions.in_the_shop.tracking_period');
        $deleteAfter = Date::now()->addDays($deleteAfterDays);
        foreach ($catalogUuids as $catalogUuid) {
            $values[] = [
                'uuid' => Uuid::uuid4()->toString(),
                'catalog_uuid' => $catalogUuid,
                'client_uuid' => $clientUuid,
                'delete_after' => $deleteAfter
            ];
        }

        PromotionInTheShopLastPurchase::upsert($values, [
            'catalog_uuid',
            'client_uuid',
        ], [
            'delete_after',
        ]);
    }
}
