<?php

namespace App\Listeners;

use App\Events\OrderIsCreating;
use App\Events\OrderStatusChanging;
use App\Events\ReceiptReceived;
use App\Models\ClientBonusTransaction;
use App\Models\OrderStatus;
use App\Models\Receipt;
use App\Services\Management\Client\Bonus\BonusTransactionData;
use App\Services\Management\Client\Bonus\BonusTransactionProducerInterface;

class ProduceClientBonus
{
    public function handle($event)
    {
        $class = get_class($event);
        $data = null;

        switch ($class) {
            case OrderIsCreating::class:
                /** @var $event OrderIsCreating */
                $order = $event->order;
                if ($order->paid_bonus <= 0) {
                    return;
                }

                $data = BonusTransactionData::create()
                    ->setClientId($order->client_uuid)
                    // Minus here!
                    ->setBonusDelta(-$order->paid_bonus)
                    ->setRelatedModel($order)
                    ->setReason(ClientBonusTransaction::REASON_PURCHASE_PAID);
                break;
            case OrderStatusChanging::class:
                /** @var OrderStatusChanging $event */
                $order = $event->order;

                switch ($event->newStatusId) {
                    case OrderStatus::ID_DONE:
                        if ($order->paid_bonus > 0 || $order->bonus_to_charge <= 0) {
                            return;
                        }

                        $data = BonusTransactionData::create()
                            ->setClientId($order->client_uuid)
                            ->setBonusDelta($order->bonus_to_charge)
                            ->setRelatedModel($order)
                            ->setReason(ClientBonusTransaction::REASON_PURCHASE_DONE);
                        break;
                    case OrderStatus::ID_CANCELLED:
                        if ($order->paid_bonus > 0) {
                            $data = BonusTransactionData::create()
                                ->setClientId($order->client_uuid)
                                ->setBonusDelta($order->paid_bonus)
                                ->setRelatedModel($order)
                                ->setReason(ClientBonusTransaction::REASON_PAID_PURCHASE_CANCELLED);
                        } elseif ($event->oldStatusId === OrderStatus::ID_DONE && $order->bonus_to_charge > 0) {
                            $data = BonusTransactionData::create()
                                ->setClientId($order->client_uuid)
                                // Minus here!
                                ->setBonusDelta(-$order->bonus_to_charge)
                                ->setRelatedModel($order)
                                ->setReason(ClientBonusTransaction::REASON_DONE_PURCHASE_CANCELLED);
                        }
                        break;
                    case OrderStatus::ID_COLLECTED:
                        /** @var ClientBonusTransaction|null $transaction */
                        $transaction = $order->relatedClientBonusTransactions()
                            ->first();
                        if (! $transaction) {
                            return;
                        }

                        if ($transaction->reason !== ClientBonusTransaction::REASON_PURCHASE_PAID) {
                            return;
                        }

                        if ($transaction->quantity_delta == -$order->paid_bonus) {
                            return;
                        }

                        $diff = (-$transaction->quantity_delta) - $order->paid_bonus;
                        $data = BonusTransactionData::create()
                            ->setClientId($order->client_uuid)
                            ->setBonusDelta($diff)
                            ->setRelatedModel($order)
                            ->setReason(ClientBonusTransaction::REASON_PAID_PURCHASE_CHANGED);
                        break;
                }

                break;
            case ReceiptReceived::class:
                /** @var ReceiptReceived $event */
                $receipt = $event->receipt;
                if ($receipt->refund_by_receipt_uuid) {
                    $refundReceipt = Receipt::find($receipt->refund_by_receipt_uuid);
                    if ($refundReceipt) {
                        if ($refundReceipt->paid_bonus > 0) {
                            $delta = $refundReceipt->paid_bonus;
                            $reason = ClientBonusTransaction::REASON_DONE_PURCHASE_CANCELLED;
                        } elseif ($refundReceipt->bonus_to_charge > 0) {
                            // Minus here!
                            $delta = -$refundReceipt->bonus_to_charge;
                            $reason = ClientBonusTransaction::REASON_PAID_PURCHASE_CANCELLED;
                        } else {
                            return;
                        }
                    } else {
                        return;
                    }
                } else {
                    if ($receipt->paid_bonus > 0) {
                        // Minus here!
                        $delta = -$receipt->paid_bonus;
                        $reason = ClientBonusTransaction::REASON_PURCHASE_PAID;
                    } elseif ($receipt->bonus_to_charge > 0) {
                        $delta = $receipt->bonus_to_charge;
                        $reason = ClientBonusTransaction::REASON_PURCHASE_DONE;
                    } else {
                        return;
                    }
                }

                $card = $receipt->loyaltyCard;
                if (! $card) {
                    return;
                }

                $clientUuid = $card->client_uuid;
                if (! $clientUuid) {
                    return;
                }

                $data = BonusTransactionData::create()
                    ->setClientId($clientUuid)
                    ->setBonusDelta($delta)
                    ->setRelatedModel($receipt)
                    ->setReason($reason);
                break;
            default:
                throw new \Exception('Bad provided event');
        }

        if (! $data) {
            return;
        }

        $this->getProducer()->produce($data);
    }

    /**
     * @return \App\Services\Management\Client\Bonus\BonusTransactionProducerInterface
     */
    protected function getProducer(): BonusTransactionProducerInterface
    {
        return app(BonusTransactionProducerInterface::class);
    }
}
