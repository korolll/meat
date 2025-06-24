<?php

namespace App\Http\Resources;

use App\Models\PromoDiverseFoodSettings;
use App\Services\Framework\Http\Resources\Json\JsonResource;


class PromoDiverseFoodSettingsResource extends JsonResource
{
    /**
     * @param PromoDiverseFoodSettings $model
     * @return array
     */
    public function resource($model)
    {
        return [
            'uuid' => $model->uuid,
            'count_purchases' => $model->count_purchases,
            'count_rating_scores' => $model->count_rating_scores,
            'discount_percent' => $model->discount_percent,
            'is_enabled' => $model->is_enabled,
        ];
    }
}
