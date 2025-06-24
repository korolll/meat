<?php

namespace App\Http\Responses;

use App\Model\Client;
use App\Http\Resources\LoyaltyCardResource;
use App\Models\LoyaltyCard;
use App\Services\Framework\Http\EloquentCollectionResponse;

class LoyaltyCardCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = LoyaltyCardResource::class;

    /**
     * @var string
     */
    protected $model = LoyaltyCard::class;

    /**
     * @var array
     */
    protected $attributeMappings = [
        'client_phone' => 'client.phone',
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'number',
        'client_phone',
        'loyalty_card_type_uuid',
        'created_at',
    ];
}
