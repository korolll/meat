<?php

namespace App\Http\Responses;

use App\Http\Resources\PromocodeResource;
use App\Models\Promocode;
use App\Services\Framework\Http\EloquentCollectionResponse;

class PromocodeCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = PromocodeResource::class;

    /**
     * @var string
     */
    protected $model = Promocode::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'description',
        'enabled',
        'discount_percent',
        'min_price',
        'created_at',
        'updated_at',
    ];
}
