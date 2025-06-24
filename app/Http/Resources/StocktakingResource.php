<?php

namespace App\Http\Resources;

use App\Models\Stocktaking;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class StocktakingResource extends JsonResource
{
    /**
     * @param Stocktaking $stocktaking
     * @return array
     */
    public function resource($stocktaking)
    {
        return [
            'uuid' => $stocktaking->uuid,
            'approved_at' => $stocktaking->approved_at,
            'created_at' => $stocktaking->created_at,
        ];
    }
}
