<?php

namespace App\Http\Resources;

use App\Models\Assortment;
use App\Models\User;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;

class AssortmentResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource, string $prefix = '')
    {
        $resource->loadMissing([
            $prefix . 'catalog' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            $prefix . 'images' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
            },
            $prefix . 'files' => function (Relation $query) {
                return $query->select('uuid', 'path', 'thumbnails');
            },
            $prefix . 'stores' => function (Relation $query) {
                return $query->orderBy('users.address','ASC')
                    ->select('users.uuid', 'users.organization_name', 'users.address', 'users.address_latitude', 'users.address_longitude', 'users.user_verify_status_id', 'products.quantity as products_quantity', 'products.price as products_price')
                    ->where('users.user_verify_status_id', '=', 'approved')
                    ->join('products', function (JoinClause $join) {
                        $join->on('products.assortment_uuid', 'assortment_matrices.assortment_uuid');
                        $join->on('products.user_uuid', 'users.uuid');
                    });
            },
            $prefix . 'assortmentProperties',
            $prefix . 'assortmentBrand',
            $prefix . 'tags',
            $prefix . 'barcodes',
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
            'short_name' => $assortment->short_name,
            'assortment_unit_id' => $assortment->assortment_unit_id,
            'country_id' => $assortment->country_id,
            'okpo_code' => $assortment->okpo_code,
            'weight' => $assortment->weight,
            'volume' => $assortment->volume,
            'manufacturer' => $assortment->manufacturer,
            'ingredients' => $assortment->ingredients,
            'description' => $assortment->description,
            'group_barcode' => $assortment->group_barcode,
            'temperature_min' => $assortment->temperature_min,
            'temperature_max' => $assortment->temperature_max,
            'production_standard_id' => $assortment->production_standard_id,
            'production_standard_number' => $assortment->production_standard_number,
            'is_storable' => $assortment->is_storable,
            'shelf_life' => $assortment->shelf_life,
            'nds_percent' => $assortment->nds_percent,
            'assortment_verify_status_id' => $assortment->assortment_verify_status_id,
            'assortment_brand_uuid' => $assortmentBrand->uuid,
            'assortment_brand_name' => $assortmentBrand->name,
            'images' => FileShortInfoResource::collection($assortment->images),
            'files' => FileShortInfoResource::collection($assortment->files),
            'properties' => AssortmentPropertyResource::collection($assortment->assortmentProperties),
            'tags' => TagNameResource::collection($assortment->tags),
            'barcodes' => $assortment->barcodes->pluck('barcode'),
            'created_at' => $assortment->created_at,
            'declaration_end_date' => $assortment->declaration_end_date,
            'article' => $assortment->article,
            'bonus_percent' => $assortment->bonus_percent,
            'stores' => $assortment->stores->map(function (User $store) {
                return [
                    'uuid' => $store->uuid,
                    'brand_name' => $store->organization_name, // @todo Исправить на имя магазина
                    'address' => $store->address,
                    'products_price' => isset($store->products_price) ? ((float) $store->products_price) : 0.0,
                    'products_quantity' => isset($store->products_quantity) ? ((float) $store->products_quantity) : 0.0,
                ];
            }),
        ];
    }
}
