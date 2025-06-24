<?php

namespace App\Http\Resources;

use App\Models\Assortment;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class AssortmentMatrixResource extends JsonResource
{
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'tags',
            'images',
            'barcodes',
            'assortmentProperties',
        ]);
    }

    /**
     * @param Assortment $assortment
     * @return array
     */
    public function resource($assortment)
    {
        return [
            'uuid' => $assortment->uuid,
            'name' => $assortment->name,
            'short_name' => $assortment->short_name,
            'barcodes' => $assortment->barcodes->pluck('barcode'),
            'week_sales' => (int) $assortment->week_sales,
            'quantity' => (int) $assortment->quantity,
            'price_min' => (float) $assortment->price_min,
            'catalog_uuid' => $assortment->catalog_uuid,
            'tags' => $assortment->tags->pluck('name'),
            'images' => FileShortInfoResource::collection($assortment->images),
            'shelf_life' => $assortment->shelf_life,
            'is_storable' => $assortment->is_storable,
            'manufacturer' => $assortment->manufacturer,
            'receipts_of_the_week' => (int) $assortment->receipts_of_the_week,
            'offs_of_the_week' => (int) $assortment->offs_of_the_week,

            // Дополнительно добирается при формировании матрицы из заявок, 
            'order_quantity' => $assortment->order_quantity ? (int) $assortment->order_quantity : null,
        ];
    }
}
