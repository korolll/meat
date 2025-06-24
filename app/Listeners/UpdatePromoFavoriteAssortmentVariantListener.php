<?php

namespace App\Listeners;

use App\Events\OrderIsDone;
use App\Events\ReceiptReceived;
use App\Jobs\ResolveClientFavoriteAssortmentVariantJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePromoFavoriteAssortmentVariantListener implements ShouldQueue
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

        ResolveClientFavoriteAssortmentVariantJob::dispatch([$card->client_uuid], true);
    }

    /**
     * @param \App\Events\OrderIsDone $event
     */
    protected function handleOrderStatus(OrderIsDone $event): void
    {
        ResolveClientFavoriteAssortmentVariantJob::dispatch([$event->order->client_uuid], true);
    }
}
