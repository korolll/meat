<?php

namespace App\Http\Resources;

use App\Models\Transportation;
use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class TransportationResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'car' => function (Relation $query) {
                return $query->select('uuid', 'license_plate');
            },
            'driver' => function (Relation $query) {
                return $query->select('uuid', 'full_name');
            },
        ]);
    }

    /**
     * @param Transportation $transportation
     * @return array
     */
    public function resource($transportation)
    {
        return [
            'uuid' => $transportation->uuid,
            'date' => $transportation->date,
            'car_uuid' => $transportation->car->uuid,
            'car_license_plate' => $transportation->car->license_plate,
            'driver_uuid' => $transportation->driver->uuid,
            'driver_full_name' => $transportation->driver->full_name,
            'transportation_status_id' => $transportation->transportation_status_id,
            'started_at' => $transportation->started_at,
            'created_at' => $transportation->created_at,
        ];
    }
}
