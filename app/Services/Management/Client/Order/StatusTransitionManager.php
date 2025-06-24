<?php

namespace App\Services\Management\Client\Order;

use App\Exceptions\ClientExceptions\OrderShouldBePaid;
use App\Exceptions\ClientExceptions\OrderStateHasChanged;
use App\Exceptions\ClientExceptions\StatusTransitionImpossibleException;
use App\Models\Order;
use App\Models\OrderPaymentType;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;

class StatusTransitionManager implements StatusTransitionManagerInterface
{
    /**
     * @var \App\Services\Management\Client\Order\OrderLockerInterface
     */
    protected OrderLockerInterface $orderLocker;

    /**
     * @var array
     */
    protected array $transitionVariants;

    /**
     * @param \App\Services\Management\Client\Order\OrderLockerInterface $orderLocker
     * @param array                                                      $transitionVariants
     */
    public function __construct(OrderLockerInterface $orderLocker, array $transitionVariants)
    {
        $this->orderLocker = $orderLocker;
        $this->transitionVariants = $transitionVariants;
    }

    /**
     * @param \App\Models\Order                          $order
     * @param \Illuminate\Contracts\Auth\Authenticatable $caller
     * @param string                                     $nextStatusId
     *
     * @return \App\Models\Order
     * @throws \App\Exceptions\ClientExceptions\StatusTransitionImpossibleException
     * @throws \Throwable
     */
    public function transition(Order $order, Authenticatable $caller, string $nextStatusId): Order
    {
        if (! $this->canTransit($order, $caller, $nextStatusId)) {
            throw new StatusTransitionImpossibleException();
        }

        $currentState = $order->order_status_id;
        return $this->orderLocker->lock($order->uuid, function (Order $locked) use ($currentState, $nextStatusId) {
            if ($locked->order_status_id !== $currentState) {
                throw new OrderStateHasChanged();
            }

            $locked->order_status_id = $nextStatusId;
            $locked->saveOrFail();

            return $locked;
        });
    }

    /**
     * @param \App\Models\Order                          $order
     * @param \Illuminate\Contracts\Auth\Authenticatable $caller
     * @param string                                     $nextStatusId
     *
     * @return bool
     */
    protected function canTransit(Order $order, Authenticatable $caller, string $nextStatusId): bool
    {
        $path = get_class($caller);
        if ($caller instanceof User) {
            $path .= '.' . $caller->user_type_id;
        }

        $path .= '.' . $order->order_status_id;
        $path .= '.' . $nextStatusId;

        $rules = Arr::get($this->transitionVariants, $path);
        if (is_array($rules)) {
            $this->tryValidate($order, $rules);
        }

        return $rules !== null;
    }

    /**
     * @param \App\Models\Order $order
     * @param array             $rules
     */
    protected function tryValidate(Order $order, array $rules): void
    {
        $ruleMethod = Arr::get($rules, 'check');
        if (! $ruleMethod) {
            return;
        }

        if (method_exists($this, $ruleMethod)) {
            call_user_func([$this, $ruleMethod], $order);
        }
    }

    /**
     * @param \App\Models\Order $order
     *
     * @throws \App\Exceptions\ClientExceptions\OrderShouldBePaid
     */
    protected function shouldBePaidOnline(Order $order): void
    {
        if ($order->order_payment_type_id !== OrderPaymentType::ID_ONLINE) {
            return;
        }

        if (! $order->is_paid) {
            throw new OrderShouldBePaid();
        }
    }
}
