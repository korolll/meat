<?php

namespace App\Http\Resources\API\Reports;

use App\Http\Resources\AssortmentResource;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class PurchasesReportResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource, string $prefix = '')
    {
        AssortmentResource::loadMissing($resource, $prefix);
    }

    /**
     * @param \App\Models\Assortment $assortment
     *
     * @return array
     */
    public function resource($assortment)
    {
        return [
            'assortment' => AssortmentResource::make($assortment),
            'total_sum' => $assortment->total_sum,
            'total_quantity' => $assortment->total_quantity,
        ];
    }
}
