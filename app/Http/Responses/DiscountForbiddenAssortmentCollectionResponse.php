<?php

namespace App\Http\Responses;

use App\Http\Resources\DiscountForbiddenAssortmentResource;
use App\Models\DiscountForbiddenAssortment;
use App\Services\Framework\Http\EloquentCollectionResponse;

class DiscountForbiddenAssortmentCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = DiscountForbiddenAssortmentResource::class;

    /**
     * @var string
     */
    protected $model = DiscountForbiddenAssortment::class;

    /**
     * @var array
     */
    protected $attributes = [
        'assortment_uuid',
        'assortment_name',
        'uuid',
        'created_at',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'assortment_name' => 'assortment.name',
    ];
}
