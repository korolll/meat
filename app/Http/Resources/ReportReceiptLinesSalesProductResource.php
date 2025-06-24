<?php

namespace App\Http\Resources;

use App\Models\ReceiptLine;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReportReceiptLinesSalesProductResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'assortment' => function (Relation $query) {
                return $query->select('uuid', 'name');
            },
            'assortment.images' => function (Relation $query) {
                return $query->select('uuid', 'file_category_id', 'path', 'thumbnails');
            },
        ]);
    }

    /**
     * @param ReceiptLine $receiptLine
     * @return array
     */
    public function resource($receiptLine)
    {
        if ($receiptLine->price_with_discount) {
            $price = $receiptLine->price_with_discount;
            $originalPrice = $receiptLine->price_with_discount + $receiptLine->discount;
            $totalDiscount = $receiptLine->discount * $receiptLine->quantity;
        } else {
            $price = $receiptLine->total / $receiptLine->quantity;
            $originalPrice = null;
            $totalDiscount = null;
        }

        $assortment = optional($receiptLine->assortment);
        return [
            'assortment_uuid' => $receiptLine->assortment_uuid,
            'assortment_name' => $assortment->name,
            'assortment_files' => AssortmentFileResource::collection($assortment->images ?: []),
            'barcode' => $receiptLine->barcode,
            'quantity' => $receiptLine->quantity,
            'total' => $receiptLine->total,
            'price' => $price,
            'original_price' => $originalPrice,
            'discount' => $receiptLine->discount,
            'total_discount' => $totalDiscount,
            'discountable_type' => $receiptLine->discountable_type,
        ];
    }
}
