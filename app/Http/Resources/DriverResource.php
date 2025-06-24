<?php

namespace App\Http\Resources;

use App\Models\Driver;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    /**
     * @param Driver $driver
     * @return array
     */
    public function resource($driver)
    {
        return [
            'uuid' => $driver->uuid,
            'full_name' => $driver->full_name,
            'email' => $driver->email,
            'hired_on' => $driver->hired_on,
            'fired_on' => $driver->fired_on,
            'comment' => $driver->comment,
            'license_number' => $driver->license_number,
            'created_at' => $driver->created_at,
        ];
    }
}
