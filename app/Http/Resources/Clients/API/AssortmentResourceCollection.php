<?php

namespace App\Http\Resources\Clients\API;

use App\Http\Resources\FileShortInfoResource;
use App\Http\Resources\TagNameResource;
use App\Models\Assortment;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use App\Services\Money\MoneyHelper;
use Illuminate\Database\Eloquent\Relations\Relation;

class AssortmentResourceCollection extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'catalog' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'rating' => function (Relation $query) {
                return $query->select('reference_type', 'reference_id', 'value');
            },
            'images' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails')->orderBy('uuid', 'asc');
            },
            'tags',
            'assortmentBrand',
            'assortmentProperties'
        ]);
    }

    /**
     * @param Assortment $assortment
     * @return array
     */
    public function resource($assortment)
    {
        $assortmentBrand = optional($assortment->assortmentBrand);

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

        return [
            'uuid' => $assortment->uuid,
            'catalog_uuid' => $assortment->catalog->uuid,
            'catalog_name' => $assortment->catalog->name,
            'name' => $assortment->name,
            'assortment_brand_uuid' => $assortmentBrand->uuid,
            'assortment_brand_name' => $assortmentBrand->name,
            'barcodes' => $assortment->barcodes->pluck('barcode'),
            'short_name' => $assortment->short_name,
            'assortment_unit_id' => $assortment->assortment_unit_id,
            'weight' => $assortment->weight,
            'volume' => $assortment->volume,
            'rating' => optional($assortment->rating)->value,
            'images' => FileShortInfoResource::collection($assortment->images),
            'manufacturer' => $assortment->manufacturer,
            'is_favorite' => $this->when(isset($assortment->is_favorite), (bool) $assortment->is_favorite),
            'is_promo_favorite' => $this->when(isset($assortment->is_promo_favorite), (bool) $assortment->is_promo_favorite),
            'tags' => TagNameResource::collection($assortment->tags),
            'properties' => AssortmentPropertyResource::collection($assortment->assortmentProperties),
            'current_price' => $this->when(isset($assortment->current_price), $assortment->current_price),
            'total_bonus' => $this->when(isset($totalBonus), $totalBonus),
            'products_quantity' => $this->when(isset($assortment->products_quantity), (float) $assortment->products_quantity),
            'price_with_discount' => $this->when(isset($assortment->price_with_discount), (float) $assortment->price_with_discount),
            'discount_type' => $this->when(isset($assortment->discount_type), $assortment->discount_type),
            'discount_type_color' => $this->when(isset($assortment->discount_type_color), $assortment->discount_type_color),
            'discount_type_name' => $this->when(isset($assortment->discount_type_name), $assortment->discount_type_name),
            'has_yellow_price' => $this->when(isset($assortment->has_yellow_price), $assortment->has_yellow_price),
            'quantity_in_client_cart' => $this->when(isset($assortment->quantity_in_client_cart), $assortment->quantity_in_client_cart),
        ];
    }
}
