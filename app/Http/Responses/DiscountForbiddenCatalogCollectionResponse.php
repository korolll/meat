<?php

namespace App\Http\Responses;

use App\Http\Resources\DiscountForbiddenCatalogResource;
use App\Models\DiscountForbiddenCatalog;
use App\Services\Framework\Http\EloquentCollectionResponse;

class DiscountForbiddenCatalogCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = DiscountForbiddenCatalogResource::class;

    /**
     * @var string
     */
    protected $model = DiscountForbiddenCatalog::class;

    /**
     * @var array
     */
    protected $attributes = [
        'catalog_uuid',
        'uuid',
        'created_at',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'catalog_name' => 'catalog.name',
    ];
}
