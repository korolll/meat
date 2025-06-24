<?php

namespace App\Http\Resources\Clients\API;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\FileShortInfoResource;
use App\Models\Assortment;
use Illuminate\Database\Query\JoinClause;

class MealReceiptResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'mealReceiptTabs' => function (Relation $query) {
                return $query->select();
            },
            'file' => function (Relation $query) {
                return $query->select('uuid', 'path');
            },
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
     * @param \App\Models\MealReceipt $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'uuid' => $resource->uuid,
            'name' => $resource->name,
            'section' => $resource->section,
            'title' => $resource->title,
            'description' => $resource->description,
            'ingredients' => $resource->ingredients,
            'duration' => $resource->duration,

            'client_like_value' => $resource->client_like_value,

            'file_path' => Storage::url($resource->file->path),
            'assortments' => $resource->assortments->map(function (Assortment $assortment) {
                return [
                    'uuid' => $assortment->uuid,
                    'name' => $assortment->name,
                    'assortment_unit_id' => $assortment->assortment_unit_id,
                    'weight' => $assortment->weight,
                    'rating' => optional($assortment->rating)->value,
                    'images' => FileShortInfoResource::collection($assortment->images),
                    'quantity' => (int) $assortment->pivot->quantity,
                    'products_quantity' => $this->when(isset($assortment->products_quantity), (float) $assortment->products_quantity),
                    'current_price' => $this->when(isset($assortment->current_price), $assortment->current_price),
                    'price_with_discount' => $this->when(isset($assortment->price_with_discount), (float) $assortment->price_with_discount),
                    'discount_type' => $this->when(isset($assortment->discount_type), $assortment->discount_type),
                    'discount_type_color' => $this->when(isset($assortment->discount_type_color), $assortment->discount_type_color),
                    'discount_type_name' => $this->when(isset($assortment->discount_type_name), $assortment->discount_type_name),
                ];
            })->all(),
            'tabs' => MealReceiptTabResource::collection($resource->mealReceiptTabs),
            'is_favorite' => $this->when(
                isset($resource->is_favorite),
                (bool) $resource->is_favorite
            ),

            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
