<?php

namespace App\Http\Responses;

use App\Http\Resources\CatalogResource;
use App\Models\Catalog;
use App\Services\Framework\Http\EloquentCollectionResponse;

class CatalogCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = CatalogResource::class;

    /**
     * @var string
     */
    protected $model = Catalog::class;

    /**
     * @var array
     */
    protected $attributes = [
        'catalog_uuid',
        'catalog_name',
        'name',
        'created_at',
        'assortments_count',
        'products_count',
        'sort_number',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'catalog_name' => 'parent.name',
        'catalog_uuid' => 'parent.uuid',
    ];
}
