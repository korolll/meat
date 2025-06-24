<?php

namespace App\Http\Responses\Clients\API\Profile;

use App\Http\Resources\Clients\API\Profile\PromoDiverseFoodClientDiscountResource;
use App\Models\PromoDiverseFoodClientDiscount;
use App\Services\Framework\Http\EloquentCollectionResponse;

class PromoDiverseFoodClientDiscountResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = PromoDiverseFoodClientDiscountResource::class;

    /**
     * @var string
     */
    protected $model = PromoDiverseFoodClientDiscount::class;

    /**
     * @var array
     */
    protected $attributes = [
        'client_uuid',
        'discount_percent',
        'start_at',
        'end_at',
        'created_at',
        'updated_at',
    ];
}
