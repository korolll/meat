<?php

namespace App\Http\Responses;

use App\Http\Resources\ReportProductsSummaryProductResource;
use App\Models\Product;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ReportProductsSummaryCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ReportProductsSummaryProductResource::class;

    /**
     * @var string
     */
    protected $model = Product::class;

    /**
     * @var array
     */
    protected $attributes = [
        'uuid',
        'assortment_uuid',
        'assortment_name',
        'catalog_uuid',
        'catalog_name',
        'delta_minus',
        'delta_plus',
        'quantity_on_start',
        'quantity_on_end',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'assortment_uuid' => 'assortment.uuid',
        'assortment_name' => 'assortment.name',
        'catalog_uuid' => 'catalog.uuid',
        'catalog_name' => 'catalog.name',
        'delta_minus' => 'inner_query.delta_minus',
        'delta_plus' => 'inner_query.delta_plus',
        'quantity_on_start' => 'inner_query.quantity_on_start',
        'quantity_on_end' => 'inner_query.quantity_on_end',
    ];
}
