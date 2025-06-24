<?php

namespace App\Http\Resources;

use App\Models\Assortment;
use App\Models\AssortmentProperty;
use App\Services\Framework\Http\Resources\Json\ResourceCollection;
use Illuminate\Database\Eloquent\Relations\Relation;

class AssortmentResourceCollection extends ResourceCollection
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
            'images' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
            },
            'assortmentBrand',
            'tags',
            'barcodes'
        ]);
    }

    /**
     * @param Assortment $assortment
     * @return array
     */
    public function resource($assortment)
    {
        $assortmentBrand = optional($assortment->assortmentBrand);

        return [
            'uuid' => $assortment->uuid,
            'catalog_uuid' => $assortment->catalog->uuid,
            'catalog_name' => $assortment->catalog->name,
            'name' => $assortment->name,
            'assortment_verify_status_id' => $assortment->assortment_verify_status_id,
            'assortment_brand_uuid' => $assortmentBrand->uuid,
            'assortment_brand_name' => $assortmentBrand->name,
            'images' => FileShortInfoResource::collection($assortment->images),
            'created_at' => $assortment->created_at,
            'shelf_life' => $assortment->shelf_life,
            'is_storable' => $assortment->is_storable,

            // Виртуальная колонка
            'price_min' => $this->when(
                isset($assortment->price_min),
                (float)$assortment->price_min
            ),

            // Виртуальная колонка
            'is_exists_in_assortment_matrix' => $this->when(
                isset($assortment->is_exists_in_assortment_matrix),
                (bool)$assortment->is_exists_in_assortment_matrix
            ),
            'barcodes' => $assortment->barcodes->pluck('barcode'),
            'tags' => TagNameResource::collection($assortment->tags),
            'article' => $assortment->article,
            'bonus_percent' => $assortment->bonus_percent,
            'current_price' => $this->when(isset($assortment->current_price), $assortment->current_price),
            'products_quantity' => $this->when(isset($assortment->products_quantity), (float) $assortment->products_quantity),
        ];
    }
}
