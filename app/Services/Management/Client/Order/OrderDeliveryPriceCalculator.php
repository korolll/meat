<?php

namespace App\Services\Management\Client\Order;

use App\Exceptions\ClientExceptions\OrderCantGeocodeClientAddress;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderDeliveryType;
use App\Models\User;
use App\Services\Management\Client\Order\System\SystemOrderSettingStorageInterface;
use Ballen\Distical\Calculator;
use Ballen\Distical\Entities\LatLong;
use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Location;
use Illuminate\Support\Arr;

class OrderDeliveryPriceCalculator implements OrderDeliveryPriceCalculatorInterface
{
    protected SystemOrderSettingStorageInterface $settingStorage;
    protected float $price;

    /**
     * @param SystemOrderSettingStorageInterface $settingStorage
     * @param array $config
     */
    public function __construct(SystemOrderSettingStorageInterface $settingStorage, array $config)
    {
        $this->settingStorage = $settingStorage;
        $this->price = Arr::get($config, 'price', 70);
    }

    /**
     * @param Order $order
     *
     * @return float
     */
    public function calculate(Order $order): float
    {
        if ($order->order_delivery_type_id === OrderDeliveryType::ID_PICKUP) {
            return 0;
        }

        if ($order->total_price_for_products_with_discount > $this->settingStorage->getDeliveryThreshold()) {
            return 0;
        }

        if (config('app.order.delivery.use_free_first_delivery') && $this->isFirstOrder($order)) {
            return 0;
        }

        // Сюда вставляем расчёт в зависимости от расстояния
        if (config('app.order.delivery.use_distance_price')){



            $clientDeliveryAddress = $order->client
                ->clientDeliveryAddresses()
                ->orderBy('updated_at', 'desc')
                ->first();

            $selectedStore = $order->store;
            if (null !== $clientDeliveryAddress) {
                return $order->delivery_price = $this->getDeliveryPriceByDistance($clientDeliveryAddress, $selectedStore);
            } else {
                $moreZonePrize = $selectedStore?->deliveryZone()?->get()?->first()?->more_zone_price;
                return $order->delivery_price = $moreZonePrize ?: config('app.order.delivery.more_zone.price');
            }
        }

        return $this->price;
    }

    /**
     * @throws OrderCantGeocodeClientAddress
     */
    protected function getDeliveryPriceByDistance($clientAddress, $storeAddress): float
    {
        $clientAddressString = "{$clientAddress['city']}, {$clientAddress['street']}, {$clientAddress['house']}";

        /** @var ProviderAndDumperAggregator $aggregator */
        $aggregator = Geocoder::geocode($clientAddressString);
        /** @var Location|null $firstAddress */
        $firstAddress = $aggregator->get()->first();
        if (! $firstAddress) {
            throw new OrderCantGeocodeClientAddress();
        }

        $coords = $firstAddress->getCoordinates();
        if (! $coords) {
            throw new OrderCantGeocodeClientAddress();
        }
        $clientAddressData['latitude'] = $coords->getLatitude();
        $clientAddressData['longitude'] = $coords->getLongitude();
        $distance = $this->calcDistanceBetweenStoreAndClient(
            $clientAddressData,
            $storeAddress);

        $lessZoneDistance = $storeAddress?->deliveryZone()?->get()?->first()?->less_zone_distance;
        $betweenZoneDistance = $storeAddress?->deliveryZone()?->get()?->first()?->between_zone_distance;
        $lessZonePrice = $storeAddress?->deliveryZone()?->get()?->first()?->less_zone_price;
        $betweenZonePrice = $storeAddress?->deliveryZone()?->get()?->first()?->between_zone_price;
        $moreZonePrice = $storeAddress?->deliveryZone()?->get()?->first()?->more_zone_price;

        if ($distance < ($lessZoneDistance ?: config('app.order.delivery.less_zone.distance'))) {
            $price = $lessZonePrice ?: config('app.order.delivery.less_zone.price');
        } elseif ($distance < ($betweenZoneDistance ?: config('app.order.delivery.between_zones.distance'))) {
            $price = $betweenZonePrice ?: config('app.order.delivery.between_zones.price');
        } else {
            $price = $moreZonePrice ?: config('app.order.delivery.more_zone.price');
        }

        return $price;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    protected function isFirstOrder(Order $order): bool
    {
        $client = $order->client;
        return ! $client->orders()->whereIn('order_status_id', [
                    OrderStatus::ID_NEW,
                    OrderStatus::ID_DONE,
                    OrderStatus::ID_COLLECTED,
                    OrderStatus::ID_DELIVERING,
                ])->exists();
    }

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
}
