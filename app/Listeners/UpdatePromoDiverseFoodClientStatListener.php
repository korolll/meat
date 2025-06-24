<?php

namespace App\Listeners;

use App\Events\OrderIsDone;
use App\Events\RatingScoreCreated;
use App\Events\ReceiptReceived;
use App\Models\Assortment;
use App\Models\Client;
use App\Models\OrderProduct;
use App\Models\PromoDiverseFoodClientStat;
use App\Models\PromoDiverseFoodClientStatAssortment;
use App\Models\ReceiptLine;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UpdatePromoDiverseFoodClientStatListener implements ShouldQueue
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
            case RatingScoreCreated::class:
                $this->handleRatingCreated($event);
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

        $assortmentUuids = $receipt->receiptLines->pluck('assortment_uuid')
            ->filter()
            ->unique();

        $this->updateClientStat(
            $card->client_uuid,
            $receipt->created_at,
            $assortmentUuids,
            true
        );
    }

    /**
     * @param \App\Events\OrderIsDone $event
     *
     * @throws \Throwable
     */
    protected function handleOrderStatus(OrderIsDone $event): void
    {
        $order = $event->order;
        $assortmentUuids = $order->orderProducts()
            ->where('order_products.quantity', '>', 0)
            ->join('products', 'products.uuid', 'order_products.product_uuid')
            ->join('assortments', 'assortments.uuid', 'products.assortment_uuid')
            ->pluck('assortments.uuid')
            ->unique();

        $this->updateClientStat(
            $order->client_uuid,
            $event->moment,
            $assortmentUuids,
            true
        );
    }

    /**
     * @param \App\Events\RatingScoreCreated $event
     *
     * @throws \Throwable
     */
    protected function handleRatingCreated(RatingScoreCreated $event): void
    {
        $score = $event->ratingScore;
        if ($score->rated_reference_type !== Assortment::MORPH_TYPE_ALIAS) {
            return;
        }

        $ratedThrough = $score->ratedThroughReference;
        $class = get_class($ratedThrough);
        switch ($class) {
            case OrderProduct::class:
                $this->handleRatingCreatedThroughOrderProduct($event, $ratedThrough);
                break;
            case ReceiptLine::class:
                $this->handleRatingCreatedThroughReceiptLine($event, $ratedThrough);
                break;
        }
    }

    /**
     * @param \App\Events\RatingScoreCreated $event
     * @param \App\Models\OrderProduct       $product
     *
     * @throws \Throwable
     */
    protected function handleRatingCreatedThroughOrderProduct(RatingScoreCreated $event, OrderProduct $product): void
    {
        $order = $product->order;

        // Проверка что оценка покупки в текущем месяце
//        $eventMonth = $event->moment->format('Y-m');
//        $orderCreatedMonth = $order->created_at->format('Y-m');
//        if ($eventMonth !== $orderCreatedMonth) {
//            return;
//        }

        $this->updateClientStat(
            $order->client_uuid,
            $event->moment,
            [$product->product->assortment_uuid],
            false
        );
    }

    /**
     * @param \App\Events\RatingScoreCreated $event
     * @param \App\Models\ReceiptLine        $line
     *
     * @throws \Throwable
     */
    protected function handleRatingCreatedThroughReceiptLine(RatingScoreCreated $event, ReceiptLine $line): void
    {
        $receipt = $line->receipt;
        if (! $line->assortment_uuid || ! $receipt->loyaltyCard || ! $receipt->loyaltyCard->client_uuid) {
            return;
        }

        // Проверка что оценка покупки в текущем месяце
//        $eventMonth = $event->moment->format('Y-m');
//        $receiptMonth = $receipt->created_at->format('Y-m');
//        if ($eventMonth !== $receiptMonth) {
//            return;
//        }

        $this->updateClientStat(
            $receipt->loyaltyCard->client_uuid,
            $event->moment,
            [$line->assortment_uuid],
            false
        );
    }

    /**
     * @param string                  $clientUuid
     * @param \Carbon\CarbonInterface $moment
     * @param iterable                $assortmentUuids
     * @param bool                    $isPurchased
     *
     * @throws \Throwable
     */
    protected function updateClientStat(string $clientUuid, CarbonInterface $moment, iterable $assortmentUuids, bool $isPurchased): void
    {
        DB::transaction(function () use ($clientUuid, $moment, $assortmentUuids, $isPurchased) {
            $client = Client::whereUuid($clientUuid)
                ->lockForUpdate()
                ->first();

            $stat = PromoDiverseFoodClientStat::firstOrCreate([
                'client_uuid' => $client->uuid,
                'month' => $moment->format('Y-m'),
            ]);

            $existRows = $stat->promoDiverseFoodClientStatAssortments->keyBy('assortment_uuid');
            $diffValue = 0;
            foreach ($assortmentUuids as $assortmentUuid) {
                /** @var ?\App\Models\PromoDiverseFoodClientStatAssortment $row */
                $row = $existRows->get($assortmentUuid);
                if ($isPurchased) {
                    if (! $row) {
                        $newRow = new PromoDiverseFoodClientStatAssortment();
                        $newRow->assortment_uuid = $assortmentUuid;
                        $newRow->promo_diverse_food_client_stat_uuid = $stat->uuid;
                        $newRow->save();
                        $diffValue++;
                    }
                } else {
                    if ($row && ! $row->is_rated) {
                        $row->is_rated = true;
                        $row->save();
                        $diffValue++;
                    }
                }
            }
            if (! $diffValue) {
                return;
            }

            if ($isPurchased) {
                $stat->purchased_count = $stat->purchased_count + $diffValue;
            } else {
                $stat->rated_count = $stat->rated_count + $diffValue;
            }

            $stat->save();
        });
    }
}
