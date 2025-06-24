<?php

namespace App\Http\Resources\Clients\API;

use App\Exceptions\ClientExceptions\OrderCantGeocodeClientAddress;
use App\Http\Resources\FileShortInfoResource;
use App\Models\ClientDeliveryAddress;
use App\Models\User;
use App\Services\Database\VirtualColumns\StoreDeliveryPrice;
use App\Services\Framework\Http\Resources\Json\ResourceCollection;
use Ballen\Distical\Calculator;
use Ballen\Distical\Entities\LatLong;
use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Location;
use Illuminate\Database\Eloquent\Relations\Relation;

class StoreResourceCollection extends ResourceCollection
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'loyaltyCardTypes' => function (Relation $query) {
                return $query->select('uuid');
            },
        ]);
    }

    /**
     * @param User $user
     * @return array|null
     */
    public function resource($user)
    {
        $deliveryPrice = $this->getDeliveryPrice($user);

        if ($deliveryPrice === null && $user->client_uuid !== StoreDeliveryPrice::EMPTY_UUID_VALUE) {
            return null;
        }

        return [
            'uuid' => $user->uuid,
            'brand_name' => $user->brand_name,
            'inn' => $user->inn,
            'work_hours_from' => $user->work_hours_from,
            'work_hours_till' => $user->work_hours_till,
            'address_latitude' => $user->address_latitude,
            'address_longitude' => $user->address_longitude,
            'loyalty_card_types' => $user->loyaltyCardTypes->map->only('uuid')->all(),
            'address' => $user->address,
            'phone' => $user->phone,

            'has_parking' => $user->has_parking,
            'has_ready_meals' => $user->has_ready_meals,
            'has_atms' => $user->has_atms,
            'image' => FileShortInfoResource::make($user->image),

            // Виртуальная колонка
            'is_favorite' => $this->when(
                isset($user->is_favorite),
                (bool) $user->is_favorite
            ),

            'delivery_price' => $deliveryPrice ? (string) round((float) $deliveryPrice) : null,
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $collection = $this->collection->map(function ($resource) {
            return $this->resource($resource);
        });

        foreach ($collection as $key => $value) {
            if (is_null($value)) {
                $collection->forget($key);
            }
        }

        return $collection->all();
    }

    private function getDeliveryPrice($user)
    {
        $storeAddress = User::query()->where('uuid', '=', $user->uuid)->first();

        if ($user->client_uuid === StoreDeliveryPrice::EMPTY_UUID_VALUE) {
            return null;
        }

        $clientAddress = ClientDeliveryAddress::query()
            ->where('client_uuid', '=', $user->client_uuid)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (! $clientAddress) {
            return null;
        }
        $clientAddressString = $clientAddress->city . ',' . $clientAddress->street . ',' . $clientAddress->house;

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

        if (
            config('app.order.delivery.use_max_distance_filter')
            && $distance > (int) config('app.order.delivery.max_distance')
        ) {
            return null;
        }

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
     * @param array            $clientAddress
     * @param \App\Models\User $store
     *
     * @return int
     */
    private function calcDistanceBetweenStoreAndClient(array $clientAddress, User $store): int
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
