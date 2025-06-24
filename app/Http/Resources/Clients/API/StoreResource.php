<?php

namespace App\Http\Resources\Clients\API;

use App\Http\Resources\FileShortInfoResource;
use App\Models\User;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class StoreResource extends JsonResource
{
    /**
     * @param User $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'loyaltyCardTypes' => function (Relation $query) {
                return $query->select(['uuid']);
            }
        ]);
    }

    /**
     * @param User $store
     * @return array
     */
    public function resource($store)
    {
        return [
            'uuid' => $store->uuid,
            'brand_name' => $store->brand_name,
            'organization_name' => $store->organization_name,
            'loyalty_card_types' => $store->loyaltyCardTypes->map->only(['uuid'])->all(),
            'address' => $store->address,
            'work_hours_from' => $store->work_hours_from,
            'work_hours_till' => $store->work_hours_till,
            'phone' => $store->phone,

            'has_parking' => $store->has_parking,
            'has_ready_meals' => $store->has_ready_meals,
            'has_atms' => $store->has_atms,
            'image' => FileShortInfoResource::make($store->image),

            // Виртуальная колонка
            'is_favorite' => $this->when(
                isset($store->is_favorite),
                (bool) $store->is_favorite
            ),

            // Виртуальная колонка дистанции
            'distance' => $this->when(
                isset($store->distance),
                (int) $store->distance
            ),
        ];
    }
}
