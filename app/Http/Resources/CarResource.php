<?php

namespace App\Http\Resources;

use App\Models\Car;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    /**
     * @param Car $car
     * @return array
     */
    public function resource($car)
    {
        return [
            'uuid' => $car->uuid,
            'brand_name' => $car->brand_name,
            'model_name' => $car->model_name,
            'license_plate' => $car->license_plate,
            'call_sign' => $car->call_sign,
            'max_weight' => $car->max_weight,
            'is_active' => $car->is_active,
            'created_at' => $car->created_at,
        ];
    }
}
