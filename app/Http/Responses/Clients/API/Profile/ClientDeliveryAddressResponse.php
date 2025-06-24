<?php

namespace App\Http\Responses\Clients\API\Profile;

use App\Http\Resources\Clients\API\Profile\DeliveryAddressResource;
use App\Models\ClientDeliveryAddress;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ClientDeliveryAddressResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = DeliveryAddressResource::class;

    /**
     * @var string
     */
    protected $model = ClientDeliveryAddress::class;

    /**
     * @var array
     */
    protected $attributes = [
        'title',
        'city',
        'created_at',
        'updated_at',
    ];
}
