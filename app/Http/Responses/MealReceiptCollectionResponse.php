<?php

namespace App\Http\Responses;

use App\Http\Resources\MealReceiptResource;
use App\Models\MealReceipt;
use App\Services\Framework\Http\EloquentCollectionResponse;

class MealReceiptCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = MealReceiptResource::class;

    /**
     * @var string
     */
    protected $model = MealReceipt::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'section',
        'title',
        'description',
        'ingredients',
        'duration',
        'created_at',
        'updated_at',
    ];
}
