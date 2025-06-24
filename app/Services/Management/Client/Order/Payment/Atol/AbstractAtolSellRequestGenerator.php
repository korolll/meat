<?php

namespace App\Services\Management\Client\Order\Payment\Atol;

use App\Models\Assortment;
use App\Models\Order;
use App\Models\OrderPaymentType;
use App\Models\OrderProduct;
use App\Models\User;
use App\Services\Money\MoneyHelper;
use App\Services\Quantity\FloatHelper;

abstract class AbstractAtolSellRequestGenerator implements AtolSellRequestGeneratorInterface
{
    const PAYMENT_METHOD_ADVANCE = 'advance';
    const PAYMENT_METHOD_FULL = 'full_payment';

    const PAYMENT_TYPE_CASH = 0;
    const PAYMENT_TYPE_BANK = 1;
    const PAYMENT_TYPE_PRE_PAY = 2;

    protected array $config = [
        'item_vat' => null,
        'delivery_vat' => 'none',
        'company_inn' => null
    ];

    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function generate(Order $order, bool $isAdvance): array
    {
        $client = $order->client;
        $user = $order->store;

        if ($isAdvance) {
            $externalId = $order->uuid . '-advanced';
            $paymentMethod = static::PAYMENT_METHOD_ADVANCE;
            $paymentType = static::PAYMENT_TYPE_PRE_PAY;
        } else {
            $paymentMethod = static::PAYMENT_METHOD_FULL;
            $paymentType = $order->order_payment_type_id === OrderPaymentType::ID_CASH
                ? static::PAYMENT_TYPE_CASH
                : static::PAYMENT_TYPE_BANK;
            $externalId = $order->uuid;
        }

        $items = [];
        $orderProducts = $order->orderProducts;
        $orderProducts->loadMissing('product.assortment');
        $agentInfo = $order->client->uuid == config('agent.client_id');

        foreach ($orderProducts as $orderProduct) {
            if (FloatHelper::isEqual($orderProduct->quantity, 0)) {
                continue;
            }

            $items[] = $this->generateItem($orderProduct, $paymentMethod, $user, $agentInfo);
        }

        if ($order->delivery_price) {
            $items[] = $this->generateDeliveryItem($order, $paymentMethod, $user, $agentInfo);
        }

        $totalPrice = $order->total_price_kopek / 100;
        $receipt = [
            'client' => [
                'email' => $client->email,
                'phone' => $client->phone
            ],
            'company' => [
                'inn' => $this->config['company_inn'] ?: $user->inn,
                'payment_address' => $user->address,
                'email' => $user->email,
                'sno' => 'usn_income' // TODO: Take from $user
            ],
            'items' => $items,
            'payments' => [
                [
                    'type' => $paymentType,
                    'sum' => $totalPrice,
                ]
            ],
            'total' => $totalPrice
        ];

        return [
            'external_id' => $externalId,
            'receipt' => $receipt,
            'timestamp' => $order->created_at->format('d.m.Y H:i:s'),
        ];
    }

    protected abstract function getItemMeasure(Assortment $assortment);

    protected abstract function getItemPaymentObjectMethod();

    protected abstract function getDeliveryItemMeasure();

    protected abstract function getDeliveryItemPaymentObjectMethod();

    protected function generateItem(OrderProduct $orderProduct, string $paymentMethod, User $user, bool $agentInfo): array
    {
        $product = $orderProduct->product;
        $assortment = $product->assortment;

        if ($this->config['item_vat']) {
            $vat = $this->config['item_vat'];
        } else {
            $vat = 'vat' . $assortment->nds_percent;
        }

        $data = [
            'name' => $assortment->name,
            'price' => $this->calculateItemPrice($orderProduct),
            'quantity' => $orderProduct->quantity,
            'sum' => $orderProduct->total_amount_with_discount,
            'measure' => $this->getItemMeasure($assortment),
            'payment_method' => $paymentMethod,
            'payment_object' => $this->getItemPaymentObjectMethod(),
            'vat' => [
                'type' => $vat
            ],
        ];

        if ($agentInfo) {
            $data['agent_info'] = $this->getAgentInfo();
            $data['supplier_info'] = $this->getSupplierInfo($user);
        }

        return $data;
    }

    protected function generateDeliveryItem(Order $order, string $paymentMethod, User $user, bool $agentInfo): array
    {
        $price = MoneyHelper::toKopek($order->delivery_price) / 100;
        $data = [
            'name' => 'Доставка',
            'price' => $price,
            'quantity' => 1.0,
            'sum' => $price,
            'measure' => $this->getDeliveryItemMeasure(),
            'payment_method' => $paymentMethod,
            'payment_object' => $this->getDeliveryItemPaymentObjectMethod(),
            'vat' => [
                'type' => $this->config['delivery_vat'] ?: 'none'
            ],
        ];

        if ($agentInfo) {
            $data['agent_info'] = $this->getAgentInfo();
            $data['supplier_info'] = $this->getSupplierInfo($user);
        }

        return $data;
    }

    protected function getAgentInfo(): array
    {
        return [
            'type' => config('agent.type'),
            'paying_agent' => [
                'operation' => config('agent.paying_agent.operation'),
                'phones' => config('agent.paying_agent.phones'),
            ],
            'money_transfer_operator' => array_filter([
                'phones' => config('agent.money_transfer_operator.phones'),
                'name' => config('agent.money_transfer_operator.name'),
                'address' => config('agent.money_transfer_operator.address'),
                'inn' => config('agent.money_transfer_operator.inn'),
            ])
        ];
    }

    protected function getSupplierInfo(User $user): array
    {
        return [
            'phones' => [$user->phone],
            'name' => $user->organization_name,
            'inn' => $user->inn,
        ];
    }

    /**
     * @param \App\Models\OrderProduct $orderProduct
     *
     * @return float
     */
    protected function calculateItemPrice(OrderProduct $orderProduct): float
    {
        if (! $orderProduct->paid_bonus) {
            return MoneyHelper::toKopek($orderProduct->price_with_discount) / 100;
        }

        $price = MoneyHelper::of($orderProduct->total_amount_with_discount)
            ->dividedBy($orderProduct->quantity);

        return MoneyHelper::toKopek($price) / 100;
    }
}
