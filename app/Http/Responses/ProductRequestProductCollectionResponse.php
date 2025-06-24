<?php

namespace App\Http\Responses;

use App\Http\Resources\ProductRequestProductResource;
use App\Models\Product;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ProductRequestProductCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ProductRequestProductResource::class;

    /**
     * @var string
     */
    protected $model = Product::class;

    /**
     * @var array
     */
    protected $attributes = [
        'assortment_uuid',
        'assortment_name',
        'barcodes',
        'quantity',
        'price',
        'weight',
        'volume',
        'is_storable',
        'product_pre_request_status',
        'product_pre_request_quantity',
        'product_pre_requests_error',
        'is_added_product'
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'assortment_uuid' => 'assortment.uuid',
        'assortment_name' => 'assortment.name',
        'quantity' => 'pivot_quantity',
        'price' => 'pivot_price',
        'weight' => 'pivot_weight',
        'volume' => 'pivot_volume',
        'is_added_product' => 'pivot_is_added_product',
        'is_storable' => 'assortment.is_storable',
    ];
}
