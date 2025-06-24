<?php

namespace App\Http\Responses;

use App\Http\Resources\AssortmentPropertyResource;
use App\Models\AssortmentProperty;
use App\Services\Framework\Http\EloquentCollectionResponse;

class AssortmentPropertyCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = AssortmentPropertyResource::class;

    /**
     * @var string
     */
    protected $model = AssortmentProperty::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'assortment_property_data_type_id',
        'is_searchable'
    ];
}
