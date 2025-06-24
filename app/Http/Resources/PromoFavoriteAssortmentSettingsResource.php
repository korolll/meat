<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;


class PromoFavoriteAssortmentSettingsResource extends JsonResource
{
    /**
     * @param \App\Models\PromoFavoriteAssortmentSetting $model
     * @return array
     */
    public function resource($model)
    {
        return [
            'uuid' => $model->uuid,
            'threshold_amount' => $model->threshold_amount,
            'number_of_sum_days' => $model->number_of_sum_days,
            'number_of_active_days' => $model->number_of_active_days,
            'discount_percent' => $model->discount_percent,

            'is_enabled' => $model->is_enabled,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }
}
