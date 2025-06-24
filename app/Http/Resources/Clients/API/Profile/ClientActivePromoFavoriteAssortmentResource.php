<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Http\Resources\FileShortInfoResource;
use App\Models\Assortment;
use Illuminate\Database\Query\JoinClause;

class ClientActivePromoFavoriteAssortmentResource extends JsonResource
{
    /**
     * @param mixed|\App\Models\ClientActivePromoFavoriteAssortment $resource
     */
    public static function loadMissing($resource)
    {
       $resource->loadMissing([
            'assortment' => function (Relation $query) {
                $query->select('assortments.*');
                if ($storeUuid = request()->get('store_uuid')) {
                    $query
                        ->leftJoin('products', function (JoinClause $join) use ($storeUuid) {
                            $join->on('products.assortment_uuid', '=', 'assortments.uuid');
                            $join->where('products.user_uuid', '=', $storeUuid);
                        })
                ->addSelect('products.price as current_price');
                }
                return $query;
            },
        ]);
    }

    /**
     * @param \App\Models\ClientActivePromoFavoriteAssortment $resource
     *
     * @return array
     */
    public function resource($resource)
    {
        return [
            'uuid' => $resource->uuid,

            'client_uuid' => $resource->client_uuid,
            'assortment_uuid' => $resource->assortment_uuid,
            'assortment_name' => $resource->assortment->name,
            'assortment_unit_id' => $resource->assortment->assortment_unit_id,
            'images' => FileShortInfoResource::collection($resource->assortment->images),
            'discount_percent' => $resource->discount_percent,

            'active_from' => $resource->active_from,
            'active_to' => $resource->active_to,

            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
            'current_price' => $resource->assortment->current_price,
            'price_with_discount' => $resource->assortment->current_price - ($resource->assortment->current_price * (($resource->discount_percent)/100)),
        ];
    }
}
