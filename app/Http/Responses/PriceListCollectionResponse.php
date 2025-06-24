<?php

namespace App\Http\Responses;

use App\Http\Resources\PriceListResource;
use App\Models\PriceList;
use App\Services\Framework\Http\EloquentCollectionResponse;

class PriceListCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = PriceListResource::class;

    /**
     * @var string
     */
    protected $model = PriceList::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'price_list_status_id',
        'customer_user_uuid',
        'date_from',
        'date_till',
        'created_at',
    ];
}
