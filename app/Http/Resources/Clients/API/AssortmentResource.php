<?php

namespace App\Http\Resources\Clients\API;

use App\Http\Resources\FileShortInfoResource;
use App\Http\Resources\TagNameResource;
use App\Models\Assortment;
use App\Models\ClientActivePromoFavoriteAssortment;
use App\Models\ClientPromotion;
use App\Models\ClientShoppingList;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Models\PromoYellowPrice;
use App\Models\User;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use App\Services\Money\MoneyHelper;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;

class AssortmentResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'rating' => function (Relation $query) {
                return $query->select('reference_type', 'reference_id', 'value');
            },
            'images' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
            },
            'stores' => function (Relation $query) {
                return $query->orderBy('users.address','ASC')
                    ->select('users.uuid', 'users.organization_name', 'users.address', 'users.address_latitude', 'users.address_longitude', 'products.quantity as products_quantity')
                    ->where('users.user_verify_status_id', '=', 'approved')
                    ->join('products', function (JoinClause $join) {
                        $join->on('products.assortment_uuid', 'assortment_matrices.assortment_uuid');
                        $join->on('products.user_uuid', 'users.uuid');
                        $join->where('products.quantity', '>', 0);
                    });
            },
            'stores.loyaltyCardTypes' => function (Relation $query) {
                return $query->select('uuid');
            },
            'catalog' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'tags',
            'assortmentProperties'
        ]);
    }

    /**
     * @param Assortment $assortment
     * @return array
     */
    public function resource($assortment)
    {
        /** @var null|\Illuminate\Database\Eloquent\Collection|\App\Models\ClientShoppingList[] $shoppingLists */
        $shoppingLists = $assortment->user_shopping_lists;
        if ($shoppingLists) {
            $shoppingLists = $shoppingLists->map(function (ClientShoppingList $list) {
                return [
                    'uuid' => $list->uuid,
                    'name' => $list->name,
                ];
            });
        }

        $totalBonus = null;
        $targetPrice = $assortment->price_with_discount ?: $assortment->current_price;
        if ($targetPrice) {
            if ($assortment->bonus_percent) {
                $totalBonus = MoneyHelper::of($targetPrice)
                    ->multipliedBy($assortment->bonus_percent)
                    ->dividedBy(100);
                $totalBonus = MoneyHelper::toBonus($totalBonus);
            } else {
                $totalBonus = 0;
            }
        }

        $discountActiveTill = null;
        if (isset($assortment->discount_active_to)) {
            $discountActiveTill = $assortment->discount_active_to;
        }

        return [
            'uuid' => $assortment->uuid,
            'catalog_uuid' => $assortment->catalog->uuid,
            'catalog_name' => $assortment->catalog->name,
            'name' => $assortment->name,
            'short_name' => $assortment->short_name,
            'assortment_unit_id' => $assortment->assortment_unit_id,
            'country_id' => $assortment->country_id,
            'weight' => $assortment->weight,
            'volume' => $assortment->volume,
            'bonus_percent' => $assortment->bonus_percent,
            'manufacturer' => $assortment->manufacturer,
            'ingredients' => $assortment->ingredients,
            'description' => $assortment->description,
            'temperature_min' => $assortment->temperature_min,
            'temperature_max' => $assortment->temperature_max,
            'production_standard_id' => $assortment->production_standard_id,
            'production_standard_number' => $assortment->production_standard_number,
            'shelf_life' => $assortment->shelf_life,
            'rating' => optional($assortment->rating)->value,
            'images' => FileShortInfoResource::collection($assortment->images),
            'stores' => $assortment->stores->map(function (User $store) {
                return [
                    'uuid' => $store->uuid,
                    'brand_name' => $store->organization_name, // @todo Исправить на имя магазина
                    'address' => $store->address,
                    'address_latitude' => $store->address_latitude,
                    'address_longitude' => $store->address_longitude,
                    'loyalty_card_types' => $store->loyaltyCardTypes->map->only('uuid')->all(),
                    'products_quantity' => isset($store->products_quantity) ? ((float) $store->products_quantity) : 0.0,
                    'work_hours_from' => $store->work_hours_from,
                    'work_hours_till' => $store->work_hours_till,
                ];
            }),
            'is_favorite' => $this->when(isset($assortment->is_favorite), (bool) $assortment->is_favorite),
            'is_promo_favorite' => $this->when(isset($assortment->is_promo_favorite), (bool) $assortment->is_promo_favorite),
            'products_quantity' => $this->when(isset($assortment->products_quantity), (float) $assortment->products_quantity),
            'tags' => TagNameResource::collection($assortment->tags),
            'properties' => AssortmentPropertyResource::collection($assortment->assortmentProperties),
            'current_price' => $this->when(isset($assortment->current_price), $assortment->current_price),
            'total_bonus' => $this->when(isset($totalBonus), $totalBonus),
            'user_shopping_lists' => $shoppingLists,
            'quantity_in_client_cart' => $this->when(isset($assortment->quantity_in_client_cart), $assortment->quantity_in_client_cart),
            'price_with_discount' => $this->when(isset($assortment->price_with_discount), (float) $assortment->price_with_discount),
            'discount_type' => $this->when(isset($assortment->discount_type), $assortment->discount_type),
            'discount_type_color' => $this->when(isset($assortment->discount_type_color), $assortment->discount_type_color),
            'discount_type_name' => $this->when(isset($assortment->discount_type_name), $assortment->discount_type_name),
            'discount_active_till' => $this->when(isset($discountActiveTill), $discountActiveTill)
        ];
    }
}
