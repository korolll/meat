<?php

namespace App\Http\Resources;

use App\Models\PromoYellowPrice;
use App\Models\User;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class PromoYellowPriceResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'stores' => function (Relation $query) {
                return $query->select('uuid', 'brand_name', 'address', 'address_latitude', 'address_longitude');
            },
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
        ]);
    }

    /**
     * @param PromoYellowPrice $model
     * @return array
     */
    public function resource($model)
    {
        return [
            'uuid' => $model->uuid,
            'assortment_uuid' => $model->assortment_uuid,
            'assortment_name' => $model->assortment->name,
            'price' => $model->price,
            'start_at' => $model->start_at,
            'end_at' => $model->end_at,
            'is_enabled' => $model->is_enabled,
            'stores' => $model->stores->map(function (User $store) {
                return [
                    'uuid' => $store->uuid,
                    'brand_name' => $store->brand_name,
                    'address' => $store->address,
                    'address_latitude' => $store->address_latitude,
                    'address_longitude' => $store->address_longitude,
                ];
            })->toArray(),
        ];
    }
}
