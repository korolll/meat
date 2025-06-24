<?php

namespace App\Http\Responses;

use App\Http\Resources\LoyaltyCardTypeResource;
use App\Models\LoyaltyCardType;
use App\Services\Framework\Http\EloquentCollectionResponse;

class LoyaltyCardTypeCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = LoyaltyCardTypeResource::class;

    /**
     * @var string
     */
    protected $model = LoyaltyCardType::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'created_at',
    ];
}
