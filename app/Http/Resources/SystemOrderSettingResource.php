<?php

namespace App\Http\Resources;

use App\Models\SystemOrderSetting;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class SystemOrderSettingResource extends JsonResource
{
    /**
     * @param SystemOrderSetting $setting
     * @return array
     */
    public function resource($setting)
    {
        return [
            'id' => $setting->id,
            'value' => $setting->value,
            'created_at' => $setting->created_at,
            'updated_at' => $setting->updated_at,
        ];
    }
}
