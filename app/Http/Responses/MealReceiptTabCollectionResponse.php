<?php

namespace App\Http\Responses;

use App\Http\Resources\MealReceiptTabResource;
use App\Models\MealReceiptTab;
use App\Services\Framework\Http\EloquentCollectionResponse;

class MealReceiptTabCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = MealReceiptTabResource::class;

    /**
     * @var string
     */
    protected $model = MealReceiptTab::class;

    /**
     * @var array
     */
    protected $attributes = [
        'id',
        'meal_receipt_uuid',
        'title',
        'text',
        'duration',
        'sequence',
        'url',
        'created_at',
        'updated_at',
    ];
}
