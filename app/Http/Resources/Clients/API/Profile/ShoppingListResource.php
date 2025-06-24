<?php

namespace App\Http\Resources\Clients\API\Profile;

use App\Http\Resources\FileShortInfoResource;
use App\Models\Assortment;
use App\Models\ClientShoppingList;
use App\Services\Database\VirtualColumns\IsAssortmentClientFavorite;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;

class ShoppingListResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortments' => function (Relation $query) {
                $query->select('assortments.uuid', 'assortments.name', 'assortments.assortment_unit_id', 'assortments.weight');
                if ($storeUuid = request()->get('store_uuid')) {
                    $query
                        ->leftJoin('products', function (JoinClause $join) use ($storeUuid) {
                            $join->on('products.assortment_uuid', '=', 'assortments.uuid');
                            $join->where('products.user_uuid', '=', $storeUuid);
                        })
                        ->addSelect('products.quantity as products_quantity')
                        ->addSelect('products.price as current_price');
                }

                $query->addVirtualColumn(IsAssortmentClientFavorite::class, 'is_favorite', [(string)optional(auth()->user())->uuid]);

                return $query;
            },
            'assortments.rating' => function (Relation $query) {
                return $query->select('reference_type', 'reference_id', 'value');
            },
            'assortments.images' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
            }
        ]);
    }

    /**
     * @param ClientShoppingList $clientShoppingList
     * @return array
     */
    public function resource($clientShoppingList)
    {
        return [
            'uuid' => $clientShoppingList->uuid,
            'name' => $clientShoppingList->name,
            'assortments' => $clientShoppingList->assortments->map(function (Assortment $assortment) {
                return [
                    'uuid' => $assortment->uuid,
                    'name' => $assortment->name,
                    'assortment_unit_id' => $assortment->assortment_unit_id,
                    'weight' => $assortment->weight,
                    'rating' => optional($assortment->rating)->value,
                    'images' => FileShortInfoResource::collection($assortment->images),
                    'is_favorite' => $this->when(isset($assortment->is_favorite), (bool) $assortment->is_favorite),
                    'quantity' => (int) $assortment->pivot->quantity,
                    'products_quantity' => $this->when(isset($assortment->products_quantity), (float) $assortment->products_quantity),
                    'current_price' => $this->when(isset($assortment->current_price), $assortment->current_price),
                    'price_with_discount' => $this->when(isset($assortment->price_with_discount), (float) $assortment->price_with_discount),
                    'discount_type' => $this->when(isset($assortment->discount_type), $assortment->discount_type),
                    'discount_type_color' => $this->when(isset($assortment->discount_type_color), $assortment->discount_type_color),
                    'discount_type_name' => $this->when(isset($assortment->discount_type_name), $assortment->discount_type_name),
                ];
            })->all(),
        ];
    }
}
