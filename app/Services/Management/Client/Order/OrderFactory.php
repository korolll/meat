<?php

namespace App\Services\Management\Client\Order;

use App\Events\OrderWithProductsCreated;
use App\Exceptions\ClientExceptions\BadProvidedOrderProductsException;
use App\Exceptions\ClientExceptions\OrderCantGeocodeClientAddress;
use App\Exceptions\ClientExceptions\OrderCantMakeForToday;
use App\Exceptions\ClientExceptions\OrderCantProcessPayment;
use App\Exceptions\ClientExceptions\OrderClientAddressTooFar;
use App\Exceptions\ClientExceptions\OrderClientDoesntHaveCreditCard;
use App\Exceptions\ClientExceptions\OrderTooLowPrice;
use App\Jobs\SendOrderCheckToAtolJob;
use App\Models\Client;
use App\Models\ClientCreditCard;
use App\Models\Order;
use App\Models\OrderDeliveryType;
use App\Models\OrderPaymentType;
use App\Models\OrderProduct;
use App\Models\PaymentVendorSetting;
use App\Models\Product;
use App\Models\User;
use App\Services\Debug\DebugDataCollector;
use App\Services\Management\Client\Order\Payment\OrderPaymentProcessorInterface;
use App\Services\Management\Client\Order\Payment\PaymentStatusEnum;
use App\Services\Management\Client\Order\System\SystemOrderSettingStorageInterface;
use App\Services\Management\Client\Product\Discount\Concrete\PromocodeDiscountResolver;
use App\Services\Quantity\FloatHelper;
use Ballen\Distical\Calculator;
use Ballen\Distical\Entities\LatLong;
use Geocoder\Laravel\Facades\Geocoder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class OrderFactory implements OrderFactoryInterface
{
    /**
     * @var \App\Services\Management\Client\Order\OrderPriceResolverInterface
     */
    private OrderPriceResolverInterface $priceResolver;

    /**
     * @var \App\Services\Management\Client\Order\Payment\OrderPaymentProcessorInterface
     */
    private OrderPaymentProcessorInterface $orderPaymentProcessor;

    /**
     * @var SystemOrderSettingStorageInterface
     */
    private SystemOrderSettingStorageInterface $settingStorage;

    /**
     * @param \App\Services\Management\Client\Order\OrderPriceResolverInterface            $priceResolver
     * @param \App\Services\Management\Client\Order\Payment\OrderPaymentProcessorInterface $orderPaymentProcessor
     */
    public function __construct(OrderPriceResolverInterface $priceResolver, OrderPaymentProcessorInterface $orderPaymentProcessor, SystemOrderSettingStorageInterface $settingStorage)
    {
        $this->priceResolver = $priceResolver;
        $this->orderPaymentProcessor = $orderPaymentProcessor;
        $this->settingStorage = $settingStorage;
    }

    /**
     * @param \App\Models\Client $client
     * @param array              $attributes
     * @param array              $assortmentsAttributes
     *
     * @return \App\Models\Order
     * @throws \App\Exceptions\ClientExceptions\BadProvidedOrderProductsException
     */
    public function make(Client $client, array $attributes, array $assortmentsAttributes): Order
    {
        $bonusesToPay = $attributes['paid_bonus'] ?? 0;
        $order = new Order($attributes);
        $order->client()->associate($client);

        PromocodeDiscountResolver::setPromocode($attributes['promocode'] ?? null);

        /** @var DebugDataCollector $debugCollection */
        $debugCollection = app(DebugDataCollector::class);

        $this->makeProducts($order, $assortmentsAttributes);
        return $debugCollection->measure('OrderFactory:make:resolve-price', function () use ($order, $bonusesToPay) {
            return $this->priceResolver->resolve($order, $bonusesToPay);
        });
    }

    /**
     * @param \App\Models\Client $client
     * @param array              $attributes
     * @param array              $assortmentsAttributes
     *
     * @return \App\Models\Order
     * @throws \Throwable
     */
    public function create(Client $client, array $attributes, array $assortmentsAttributes): Order
    {
        $order = $this->make($client, $attributes, $assortmentsAttributes);
        $minPrice = $this->settingStorage->getMinPrice();
        if ($minPrice > 0 && $order->total_price_for_products_with_discount <= $minPrice) {
            throw new OrderTooLowPrice($minPrice);
        }

        if ($order->order_delivery_type_id === OrderDeliveryType::ID_DELIVERY) {
            $this->resolveDelivery($order);
        }

        if ($order->order_payment_type_id === OrderPaymentType::ID_ONLINE) {
            $clientCard = $this->resolveCreditCard($client, $order->store, $order->clientCreditCard);
            if (! $clientCard) {
                throw new OrderClientDoesntHaveCreditCard();
            }

            $order->clientCreditCard()->associate($clientCard);
        }

        $cantMakeAfter = config('app.order.meta.cant_make_for_today_after');
        if ($cantMakeAfter) {
            $now = now();
            $restrictedDatetime = now()->setTimeFromTimeString($cantMakeAfter)->setSecond(0);
            $todayEnd = now()->endOfDay();
            if ($now->lessThan($todayEnd) && $now->greaterThan($restrictedDatetime)) {
                if ($order->order_delivery_type_id === OrderDeliveryType::ID_PICKUP) {
                    throw new OrderCantMakeForToday();
                }

                $targetDate = $order->planned_delivery_datetime_from;
                if ($targetDate->lessThan($todayEnd)) {
                    throw new OrderCantMakeForToday();
                }
            }
        }

        $this->resolveWasPromocodeApplied($order);

        return DB::transaction(function () use ($client, $order) {
            $order->saveOrFail();

            foreach ($order->orderProducts as $product) {
                $product->order_uuid = $order->uuid;
            }

            $order->orderProducts->saveOrFail();
            // Refresh to get `number` field from DB (it is auto-increment)
            $order->refresh();

            if ($order->order_payment_type_id === OrderPaymentType::ID_ONLINE) {
                try {
                    $payment = $this->orderPaymentProcessor->process($order);
                    if (! $payment || $payment->order_status !== PaymentStatusEnum::APPROVED) {
                        Log::channel('payments')->error('Something wrong with payment', [
                            'payment_created' => (bool)$payment,
                            'payment_status' => $payment?->order_status,
                            'order_uuid' => $order->uuid,
                        ]);

                        throw new OrderCantProcessPayment($payment?->error_message ?? 'Не удалось создать платеж');
                    }
                } catch (OrderCantProcessPayment $exception) {
                    throw $exception;
                } catch (Throwable $exception) {
                    Log::channel('payments')->error($exception->getMessage(), [
                        'exception' => $exception,
                        'order_uuid' => $order->uuid,
                    ]);
                    throw new OrderCantProcessPayment('Системная ошибка');
                }

                SendOrderCheckToAtolJob::dispatch($order, true)->afterCommit();
            }

            OrderWithProductsCreated::dispatch($order);
            return $order;
        });
    }

    /**
     * @throws \App\Exceptions\ClientExceptions\BadProvidedOrderProductsException
     */
    protected function makeProducts(Order $order, array $assortmentsAttributes): void
    {
        $assortmentUuids = [];
        foreach ($assortmentsAttributes as &$assortmentAttrs) {
            $assortmentAttrs['quantity'] = FloatHelper::round($assortmentAttrs['quantity']);
            $assortmentUuids[] = $assortmentAttrs['assortment_uuid'];
        }

        $storeProducts = Product::whereUserUuid($order->store_user_uuid)
            ->whereIn('assortment_uuid', $assortmentUuids)
            ->get()
            ->keyBy('assortment_uuid');

        if ($storeProducts->count() !== count($assortmentsAttributes)) {
            throw new BadProvidedOrderProductsException();
        }

        $storeProducts->loadMissing('assortment');
        $products = new Collection();
        foreach ($assortmentsAttributes as $assortmentAttributes) {
            $product = new OrderProduct($assortmentAttributes);
            $product->order_uuid = $order->uuid;
            $product->product()->associate($storeProducts[$assortmentAttributes['assortment_uuid']]);
            $products->add($product);
        }
        $order->setRelation('orderProducts', $products);
    }

    /**
     * @param \App\Models\Order $order
     *
     * @throws \App\Exceptions\ClientExceptions\OrderCantGeocodeClientAddress
     * @throws \App\Exceptions\ClientExceptions\OrderClientAddressTooFar
     */
    protected function resolveDelivery(Order $order): void
    {
        if (! $this->tryGeocode($order)) {
            throw new OrderCantGeocodeClientAddress();
        }

        $store = $order->store;
        $maxDistance = $store?->deliveryZone()?->get()?->first()?->max_zone_distance ?: config('app.order.delivery.max_distance');
        $distance = $this->calcDistanceBetweenStoreAndClient($order->client_address_data, $order->store);
        if ($distance > $maxDistance) {
            throw new OrderClientAddressTooFar();
        }
    }

    protected function getDeliveryDistancePrice($clientAddress, $storeAddress)
    {
        $distance = $this->calcDistanceBetweenStoreAndClient($clientAddress, $storeAddress);

        $lessZoneDistance = $storeAddress?->deliveryZone()?->get()?->first()?->less_zone_distance;
        $betweenZoneDistance = $storeAddress?->deliveryZone()?->get()?->first()?->between_zone_distance;
        $lessZonePrice = $storeAddress?->deliveryZone()?->get()?->first()?->less_zone_price;
        $betweenZonePrice = $storeAddress?->deliveryZone()?->get()?->first()?->between_zone_price;
        $moreZonePrice = $storeAddress?->deliveryZone()?->get()?->first()?->more_zone_price;

        if ($distance < ($lessZoneDistance ?: config('app.order.delivery.less_zone.distance'))) {
            return $lessZonePrice ?: config('app.order.delivery.less_zone.price');
        } elseif ($distance < ($betweenZoneDistance ?: config('app.order.delivery.between_zones.distance'))) {
            return $betweenZonePrice ?: config('app.order.delivery.between_zones.price');
        } else {
            return $moreZonePrice ?: config('app.order.delivery.more_zone.price');
        }
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return bool
     */
    protected function tryGeocode(Order $order): bool
    {
        $addressData = $order->client_address_data;
        $address = $addressData['address'];
        /** @var \Geocoder\Laravel\ProviderAndDumperAggregator $aggregator */
        $aggregator = Geocoder::geocode($address);
        /** @var \Geocoder\Location|null $firstAddress */
        $firstAddress = $aggregator->get()->first();
        if (! $firstAddress) {
            return false;
        }

        $coords = $firstAddress->getCoordinates();
        if (! $coords) {
            return false;
        }
        $addressData['latitude'] = $coords->getLatitude();
        $addressData['longitude'] = $coords->getLongitude();

        $geocodedAddressData = [
            $firstAddress->getLocality(),
            $firstAddress->getSubLocality(),
            $firstAddress->getStreetName(),
            $firstAddress->getStreetNumber(),
        ];

        $addressData['address'] = join(', ', array_filter($geocodedAddressData));
        $order->client_address_data = $addressData;
        return true;
    }

    /**
     * @param array            $clientAddress
     * @param \App\Models\User $store
     *
     * @return int
     */
    protected function calcDistanceBetweenStoreAndClient(array $clientAddress, User $store): int
    {
        // Set our Lat/Long coordinates
        $clientPoint = new LatLong($clientAddress['latitude'], $clientAddress['longitude']);
        $storePoint = new LatLong($store->address_latitude, $store->address_longitude);

        // Get the distance between these two Lat/Long coordinates...
        $distanceCalculator = new Calculator($clientPoint, $storePoint);

        // You can then compute the distance...
        $distance = $distanceCalculator->get();
        return (int)($distance->asKilometres() * 1000);
    }

    protected function resolveCreditCard(Client $client, User $store, ?ClientCreditCard $card): ?ClientCreditCard
    {
        /** @var ?PaymentVendorSetting $setting */
        $setting = $store->paymentVendorSettingsIsActive()->first();
        $settingId = $setting?->uuid;

        if ($card) {
            // Need to validate
            if ($card->payment_vendor_setting_uuid !== $settingId || !$card->binding_id) {
                throw new BadRequestHttpException('Invalid credit card provided');
            }

            return $card;
        }

        /** @var \App\Models\ClientCreditCard $clientCard */
        $clientCard = $client->clientCreditCards()
            ->whereNotNull('binding_id')
            ->where('payment_vendor_setting_uuid', $settingId)
            ->first();

        return $clientCard;
    }

    protected function resolveWasPromocodeApplied(Order $order)
    {
        if ($order->promocode && ! PromocodeDiscountResolver::getPromocode()) {
            $order->promocode = null;
        }
    }
}
