<?php

namespace App\Services\Management\Client\Order\Payment;

use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\Money\Acquire\AcquireInterface;
use App\Services\Money\Acquire\Data\PaymentStatusDto;
use App\Services\Money\Acquire\Resolver\AcquireResolverInterface;
use App\Services\Money\MoneyHelper;
use Brick\Money\RationalMoney;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class OrderPaymentProcessor implements OrderPaymentProcessorInterface
{
    protected AcquireResolverInterface $resolver;

    protected AcquireInterface|null $resolvedAcquire = null;
    protected string|null $resolvedAcquireByOrderId = null;

    /**
     * @param AcquireResolverInterface $resolver
     */
    public function __construct(AcquireResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function process(Order $order): ?ClientPayment
    {
        $current = $this->getCurrentPaymentsSituation($order);
        Log::channel('payments')->debug('Processing payment', [
            'order_uuid' => $order->uuid,
            'order_status' => $order->order_status_id,
            'payments_state' => [
                'depositedPayments' => $current['depositedPayments']->pluck('uuid'),
                'unprocessedPayment' => $current['unprocessedPayment'] ? $current['unprocessedPayment']->uuid : null,
                'approvedPayment' => $current['approvedPayment'] ? $current['approvedPayment']->uuid : null,
                'currentSum' => MoneyHelper::toKopek($current['currentSum']),
            ]
        ]);

        if (
            ! $current['unprocessedPayment']
            && ! $current['approvedPayment']
            && $current['currentSum']->isEqualTo(0)
            && $order->order_status_id === OrderStatus::ID_NEW
        ) {
            // Initial state. Process payment
            return $this->createPayment($order->total_price_kopek, $order->client, $order, false);
        }

        if ($current['unprocessedPayment']) {
            $result = $this->processUnprocessed($order, $current['unprocessedPayment']);
            if ($result->order_status === PaymentStatusEnum::CREATED) {
                // bad state
                return $result;
            } else {
                $current = $this->getCurrentPaymentsSituation($order);
            }
        }

        if ($current['approvedPayment']) {
            $paidSum = MoneyHelper::of($current['approvedPayment']->amount / 100);
        } else {
            $paidSum = $current['currentSum'];
        }

        $diff = $paidSum->minus($order->total_price);
        $diffInKopek = MoneyHelper::toKopek($diff);
        Log::channel('payments')->debug('Processing payment diff result', [
            'order_uuid' => $order->uuid,
            'diff_in_kopek' => $diffInKopek
        ]);

        if ($order->order_status_id === OrderStatus::ID_CANCELLED) {
            if ($current['approvedPayment']) {
                // Just return payment sum
                return $this->reverse($order, $current['approvedPayment'], true);
            }

            $toRefund = MoneyHelper::toKopek($paidSum);
            if ($toRefund <= 0) {
                return $current['depositedPayments']->first();
            }

            return $this->refundAll($current['depositedPayments'], $order);
        }

        if ($diffInKopek === 0) {
            // No diff
            if ($current['approvedPayment']) {
                // Need deposit with the same sum
                return $this->deposit($order->total_price_kopek, $order, $current['approvedPayment']);
            }

            if (!$order->is_paid) {
                // Looks like we tried to pay several times, and finally we are success
                $order->is_paid = true;
                $order->save();
            }

            return $current['depositedPayments']->first();
        } elseif ($diffInKopek < 0) {
            if ($current['approvedPayment']) {
                // Deposit for the original sum
                $result = $this->deposit($current['approvedPayment']->amount, $order, $current['approvedPayment'], false);
                if ($result->order_status !== PaymentStatusEnum::DEPOSITED) {
                    // Bad state
                    return $result;
                }
            }

            // Need extra payment
            $result = $this->createPayment(-$diffInKopek, $order->client, $order, true, false);
            if ($result->order_status === PaymentStatusEnum::DEPOSITED) {
                // ALL is OK
                $order->is_paid = true;
                $order->save();
            }

            return $result;
        } else {
            if ($current['approvedPayment']) {
                // Need deposit with less sum
                return $this->deposit($order->total_price_kopek, $order, $current['approvedPayment']);
            }

            // We need to refund some
            return $this->refund($diffInKopek, $order, $current['depositedPayments']->first());
        }
    }

    /**
     * @param \App\Models\Order         $order
     * @param \App\Models\ClientPayment $payment
     *
     * @return \App\Models\ClientPayment
     */
    protected function processUnprocessed(Order $order, ClientPayment $payment): ClientPayment
    {
        // First, update state
        $res = $this->updateCurrentState($order, $payment);
        if (! $res) {
            return $payment;
        }

        $acquire = $this->resolveAcquire($order);
        if ($acquire->isConfirmationNeeded($res)) {
            try {
                return $this->paymentByBinding($order, $payment);
            } catch (\Throwable $exception) {
                Log::channel('payments')->error($exception->getMessage(), [
                    'exception' => $exception,
                    'order_uuid' => $order->uuid,
                    'payment_uuid' => $payment->uuid
                ]);

                $payment->error_message = 'Не удалось проверить статус платежа';
                $payment->save();
            }
        }

        return $payment;
    }

    /**
     * @param int                       $amount
     * @param \App\Models\Order         $order
     * @param \App\Models\ClientPayment $payment
     * @param bool                      $updateOrder
     * @param bool                      $isPaidOrderState
     *
     * @return \App\Models\ClientPayment
     */
    protected function refund(int $amount, Order $order, ClientPayment $payment, bool $updateOrder = false, bool $isPaidOrderState = false): ClientPayment
    {
        $acquire = $this->resolveAcquire($order);
        try {
            $acquire->refund($payment->generated_order_uuid, $amount);
            $payment->refunded_amount = $amount;
            $payment->order_status = PaymentStatusEnum::REFUNDED;
            $payment->save();

            if ($updateOrder) {
                $order->is_paid = $isPaidOrderState;
                $order->save();
            }
        } catch (\Throwable $exception) {
            Log::channel('payments')->error('Refund order error', [
                'exception' => $exception,
                'order_uuid' => $order->uuid,
                'payment_uuid' => $payment->uuid
            ]);

            return $payment;
        }

        return $payment;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection|\App\Models\ClientPayment[] $payments
     *
     * @return void
     */
    protected function refundAll(Collection $payments, Order $order): ClientPayment
    {
        foreach ($payments as $payment) {
            $res = $this->refund(
                $payment->amount,
                $order,
                $payment
            );

            if ($res->order_status !== PaymentStatusEnum::REFUNDED) {
                // Stop process if it's not success
                return $res;
            }
        }

        $order->is_paid = false;
        $order->save();

        return $payments->first();
    }

    /**
     * @param \App\Models\Order         $order
     * @param \App\Models\ClientPayment $payment
     * @param bool                      $updateOrder
     * @param bool                      $isPaidOrderState
     *
     * @return \App\Models\ClientPayment
     */
    protected function reverse(Order $order, ClientPayment $payment, bool $updateOrder = false, bool $isPaidOrderState = false): ClientPayment
    {
        $acquire = $this->resolveAcquire($order);
        try {
            $acquire->reverse($payment->generated_order_uuid);
            $payment->order_status = PaymentStatusEnum::REVERSED;
            $payment->save();

            if ($updateOrder) {
                $order->is_paid = $isPaidOrderState;
                $order->save();
            }
        } catch (\Throwable $exception) {
            Log::channel('payments')->error('Reverse order error', [
                'exception' => $exception,
                'order_uuid' => $order->uuid,
                'payment_uuid' => $payment->uuid
            ]);

            return $payment;
        }

        return $payment;
    }

    /**
     * @param int                       $amount
     * @param \App\Models\Order         $order
     * @param \App\Models\ClientPayment $payment
     * @param bool                      $updateOrder
     * @param bool                      $isPaidOrderState
     *
     * @return \App\Models\ClientPayment
     */
    protected function deposit(int $amount, Order $order, ClientPayment $payment, bool $updateOrder = true, bool $isPaidOrderState = true): ClientPayment
    {
        $acquire = $this->resolveAcquire($order);
        try {
            $acquire->deposit($payment->generated_order_uuid, $amount);
            $payment->order_status = PaymentStatusEnum::DEPOSITED;
            $payment->save();

            if ($updateOrder) {
                $order->is_paid = $isPaidOrderState;
                $order->save();
            }
        } catch (\Throwable $exception) {
            Log::channel('payments')->error('Deposit order error', [
                'exception' => $exception,
                'order_uuid' => $order->uuid,
                'payment_uuid' => $payment->uuid
            ]);

            return $payment;
        }

        return $payment;
    }

    /**
     * @param Order $order
     * @param ClientPayment $payment
     *
     * @return PaymentStatusDto|null
     */
    protected function updateCurrentState(Order $order, ClientPayment $payment): ?PaymentStatusDto
    {
        $acquire = $this->resolveAcquire($order);
        try {
            $result = $acquire->getPaymentStatus($payment->generated_order_uuid);
        } catch (\Throwable $exception) {
            Log::channel('payments')->error($exception->getMessage(), [
                'exception' => $exception,
                'order_uuid' => $order->uuid,
                'payment_uuid' => $payment->uuid
            ]);

            $payment->error_message = 'Не удалось получить текущий статус платежа';
            $payment->save();
            return null;
        }

        $externalStatus = $result->status;
        if ($payment->order_status === $externalStatus) {
            return $result;
        }

        $payment->external_status = (string)$result->originalStatus;
        $payment->order_status = $result->status;
        $payment->save();
        return $result;
    }

    /**
     * @param int                $price
     * @param \App\Models\Client $client
     * @param \App\Models\Order  $order
     * @param bool               $oldPaymentIsFound
     * @param bool               $isHold
     *
     * @return \App\Models\ClientPayment|null
     */
    protected function createPayment(int $price, Client $client, Order $order, bool $oldPaymentIsFound, bool $isHold = true): ?ClientPayment
    {
        $acquire = $this->resolveAcquire($order);
        $creditCard = $order->clientCreditCard;
        $payment = null;
        try {
            $targetNumber = $order->number;
            if ($oldPaymentIsFound) {
                // Count number of payments
                $numberOfPayments = $order->relatedClientPayments()->count();
                if ($numberOfPayments > 0) {
                    $targetNumber .= '-' . $numberOfPayments;
                }
            }

            $result = $acquire->registerAutoPayment(
                $creditCard->binding_id,
                $client->uuid,
                $targetNumber,
                $price,
                route('web.success-payment'),
                route('web.error-payment'),
                $isHold
            );

            $payment = new ClientPayment();
            $payment->client()->associate($client);
            $payment->relatedReference()->associate($order);
            $payment->generated_order_uuid = $result->id;
            $payment->external_status = (string)$result->statusDto->originalStatus;
            $payment->order_status = $result->statusDto->status;
            $payment->binding_id = $creditCard->binding_id;
            $payment->amount = $price;
            $payment->save();

            if ($acquire->isConfirmationNeeded($result->statusDto)) {
                $this->paymentByBinding($order, $payment, $isHold ? PaymentStatusEnum::APPROVED : PaymentStatusEnum::DEPOSITED);
            }
        } catch (\Throwable $exception) {
            Log::channel('payments')->error($exception->getMessage(), [
                'exception' => $exception,
                'order_uuid' => $order->uuid,
                'payment_uuid' => $payment ? $payment->uuid : null,
                'additional' => $payment ? 'Не удалось создать платеж' : 'Не удалось провести платеж'
            ]);

            if ($payment && ! $payment->error_message) {
                $payment->error_message = 'Не удалось провести платеж. Системная ошибка';
                $payment->save();
            }
        }

        return $payment;
    }

    /**
     * @param \App\Models\Client $client
     * @param \App\Models\Order  $order
     *
     * @return array
     */
    protected function makePaymentData(Client $client, Order $order): array
    {
        // Return without card
        return ['features' => 'AUTO_PAYMENT'];

//        if (! config('app.order.payment.enable_new_data')) {
//            $acceptedClientUuids = config('app.order.payment.enable_new_data_for_clients') ?: [];
//            if (! isset($acceptedClientUuids[$client->uuid])) {
//                return ['features' => 'AUTO_PAYMENT'];
//            }
//        }
//
//        /** @var PaymentOrderBundleGeneratorInterface $generator */
//        $generator = app(PaymentOrderBundleGeneratorInterface::class);
//
//        /**
//         * Все параметры тут
//         * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:start#%D0%BE%D0%BF%D0%BB%D0%B0%D1%82%D0%B0_%D1%81_%D1%80%D0%B5%D0%B3%D0%B8%D1%81%D1%82%D1%80%D0%B0%D1%86%D0%B8%D0%B5%D0%B9_%D1%87%D0%B5%D0%BA%D0%BE%D0%B2_%D0%BD%D0%B0_%D0%BA%D0%BE%D0%BD%D1%82%D1%80%D0%BE%D0%BB%D1%8C%D0%BD%D0%BE-%D0%BA%D0%B0%D1%81%D1%81%D0%BE%D0%B2%D0%BE%D0%B9_%D1%82%D0%B5%D1%85%D0%BD%D0%B8%D0%BA%D0%B5
//         */
//        $data = ['features' => 'AUTO_PAYMENT'];
//
//        /**
//         * Next: taxSystem
//         * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:params:taxsystem
//         */
//        $data['taxSystem'] = 5;
//        $data['orderBundle'] = $generator->generate($client, $order);
//
//        return $data;
    }

    /**
     * @param Order $order
     * @param ClientPayment $payment
     * @param PaymentStatusEnum|null $targetStatus
     *
     * @return ClientPayment
     */
    protected function paymentByBinding(Order $order, ClientPayment $payment, ?PaymentStatusEnum $targetStatus = null): ClientPayment
    {
        $acquire = $this->resolveAcquire($order);
        $bindResult = $acquire->paymentOrderBinding(
            $payment->generated_order_uuid,
            $payment->binding_id
        );
        if ($bindResult) {
            $errorMessage = 'Не удалось провести платеж: ' . $bindResult;
            $payment->error_message = $errorMessage;
            $payment->save();
        } else {
            if ($targetStatus) {
                $payment->order_status = $targetStatus;
                $payment->save();
            }

            $this->updateCurrentState($order, $payment);
        }

        return $payment;
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return array{depositedPayments: Collection|ClientPayment[], currentSum: RationalMoney, unprocessedPayment: ?ClientPayment, approvedPayment: ?ClientPayment}
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    protected function getCurrentPaymentsSituation(Order $order)
    {
        $currentSum = MoneyHelper::of(0);
        $depositedPayments = new Collection();
        $unprocessedPayment = null;
        $approvedPayment = null;
        foreach ($this->findAllPayments($order) as $payment) {
            switch ($payment->order_status) {
                case PaymentStatusEnum::DEPOSITED:
                    $depositedPayments[] = $payment;
                    $currentSum = $currentSum->plus($payment->amount / 100);
                    break;
                case PaymentStatusEnum::REFUNDED:
                    $currentSum = $currentSum->plus($payment->amount / 100);
                    $currentSum = $currentSum->minus($payment->refunded_amount / 100);
                    break;
                case PaymentStatusEnum::CREATED:
                    $unprocessedPayment = $payment;
                    break;
                case PaymentStatusEnum::APPROVED:
                    $approvedPayment = $payment;
                    break;
                default:
                    // Don't collect
                    break;
            }
        }

        return compact('depositedPayments', 'currentSum', 'unprocessedPayment', 'approvedPayment');
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return \Illuminate\Database\Eloquent\Collection<ClientPayment>|ClientPayment[]
     */
    protected function findAllPayments(Order $order): Collection
    {
        /** @var \App\Models\ClientPayment $payment */
        return $order->relatedClientPayments()
            ->orderBy('created_at')
            ->get();
    }

    protected function resolveAcquire(Order $order): AcquireInterface
    {
        $id = $order->uuid;
        if ($this->resolvedAcquireByOrderId === $id) {
            return $this->resolvedAcquire;
        }

        $resolved = $this->resolver->resolveByClientCard($order->clientCreditCard);
        $this->resolvedAcquire = $resolved;
        $this->resolvedAcquireByOrderId = $id;

        return $this->resolvedAcquire;
    }
}
