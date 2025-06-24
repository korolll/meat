<?php

namespace App\Http\Resources;

use App\Models\Transportation;
use App\Services\Framework\Http\Resources\Json\ResourceCollection;
use Illuminate\Database\Eloquent\Relations\Relation;

class TransportationResourceCollection extends ResourceCollection
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
            'transportationPoints' => function (Relation $query) {
                return $query->select('uuid', 'transportation_uuid', 'address');
            },
        ]);
    }

    /**
     * @param Transportation $transportation
     * @return array
     */
    public function resource($transportation)
    {
        $firstTransportationPoint = optional($transportation->transportationPoints->first());
        $lastTransportationPoint = optional($transportation->transportationPoints->last());

        return [
            'uuid' => $transportation->uuid,
            'date' => $transportation->date,
            'car_uuid' => $transportation->car->uuid,
            'car_license_plate' => $transportation->car->license_plate,
            'driver_uuid' => $transportation->driver->uuid,
            'driver_full_name' => $transportation->driver->full_name,
            'first_transportation_point_uuid' => $firstTransportationPoint->uuid,
            'first_transportation_point_address' => $firstTransportationPoint->address,
            'last_transportation_point_uuid' => $lastTransportationPoint->uuid,
            'last_transportation_point_address' => $lastTransportationPoint->address,
            'transportation_status_id' => $transportation->transportation_status_id,
            'started_at' => $transportation->started_at,
            'created_at' => $transportation->created_at,
        ];
    }
}
