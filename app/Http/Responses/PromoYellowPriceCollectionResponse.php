<?php

namespace App\Http\Responses;

use App\Http\Resources\PromoYellowPriceResource;
use App\Models\PromoYellowPrice;
use App\Services\Framework\Http\EloquentCollectionResponse;


class PromoYellowPriceCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = PromoYellowPriceResource::class;

    /**
     * @var string
     */
    protected $model = PromoYellowPrice::class;

    /**
     * @var array
     */
    protected $attributes = [
        'uuid',
        'assortment_uuid',
        'assortment_name',
        'price',
        'start_at',
        'end_at',
        'is_enabled',
        'stores'
    ];

        /**
     * @var array
     */
    protected $attributeMappings = [
        'assortment_name' => 'assortment.name'
    ];
}
