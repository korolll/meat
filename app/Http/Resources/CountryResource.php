<?php

namespace App\Http\Resources;

use App\Models\Country;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * @param Country $country
     * @return array
     */
    public function resource($country)
    {
        return [
            'id' => $country->id,
            'name' => $country->name,
        ];
    }
}
