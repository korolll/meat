<?php

namespace App\Listeners;

use App\Events\OrderWithProductsCreated;
use App\Events\OrderProductChanged;
use App\Events\OrderStatusChanging;
use App\Models\OrderStatus;
use App\Services\Storaging\Warehouse\OrderWarehouseTransactionProducer;

class ProduceOrderRelatedWarehouseTransactions
{
    public function handle($event)
    {
        $class = get_class($event);
        switch ($class) {
            case OrderWithProductsCreated::class:
                /** @var $event OrderWithProductsCreated */
                $this->getProducer()
                    ->setOrderLineModifier(-1)
                    ->produce($event->order);
                break;
            case OrderStatusChanging::class:
                /** @var $event OrderStatusChanging */
                if ($event->newStatusId === OrderStatus::ID_CANCELLED) {
                    $this->getProducer()
                        ->produce($event->order);
                } elseif ($event->oldStatusId === OrderStatus::ID_CANCELLED) {
                    $this->getProducer()
                        ->setOrderLineModifier(-1)
                        ->produce($event->order);
                }

                break;
            case OrderProductChanged::class:
                /** @var $event OrderProductChanged */
                $this->getProducer()
                    ->setChangedProduct($event->orderProduct, $event->newQuantity - $event->oldQuantity)
                    ->produce($event->orderProduct->order);
                break;
            default:
                throw new \Exception('Bad provided event');
        }
    }

    /**
     * @return \App\Services\Storaging\Warehouse\OrderWarehouseTransactionProducer
     */
    protected function getProducer(): OrderWarehouseTransactionProducer
    {
        return app(OrderWarehouseTransactionProducer::class);
    }
}
