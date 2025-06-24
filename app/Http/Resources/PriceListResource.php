<?php

namespace App\Http\Resources;

use App\Models\PriceList;
use App\Models\User;
use App\Services\Framework\Http\Resources\Json\JsonResource;

class PriceListResource extends JsonResource
{
    /**
     * @param PriceList $priceList
     * @return array
     */
    public function resource($priceList)
    {
        /** @var User $forUser */
        $forUser = optional($priceList->customerUser);
        return [
            'uuid' => $priceList->uuid,
            'name' => $priceList->name,
            'customer_user_uuid' => $priceList->customer_user_uuid,
            'customer_user_organization_name' => $forUser->organization_name,
            'price_list_status_id' => $priceList->price_list_status_id,
            'date_from' => $priceList->date_from,
            'date_till' => $priceList->date_till,
            'created_at' => $priceList->created_at,
        ];
    }
}
