<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReportProductsSummaryProductResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name', 'catalog_uuid');
            },
            'assortment.catalog' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'assortment.barcodes',
        ]);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function resource($product)
    {
        return [
            'uuid' => $product->uuid,
            'assortment_uuid' => $product->assortment->uuid,
            'assortment_name' => $product->assortment->name,
            'barcodes' => $product->assortment->barcodes->pluck('barcode'),
            'catalog_uuid' => $product->assortment->catalog->uuid,
            'catalog_name' => $product->assortment->catalog->name,
            'delta_minus' => $product->delta_minus,
            'delta_plus' => $product->delta_plus,
            'quantity_on_start' => $product->quantity_on_start,
            'quantity_on_end' => $product->quantity_on_end,
        ];
    }
}
