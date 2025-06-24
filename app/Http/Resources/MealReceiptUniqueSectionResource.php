<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;

class MealReceiptUniqueSectionResource extends JsonResource
{
    /**
     * @param \App\Models\MealReceipt $resource
     *
     * @return array
     */
    public function resource($resource): array
    {
        return [
            'section' => $resource->section,
        ];
    }
}
