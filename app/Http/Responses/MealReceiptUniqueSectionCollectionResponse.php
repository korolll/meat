<?php

namespace App\Http\Responses;

use App\Http\Resources\MealReceiptUniqueSectionResource;
use App\Models\MealReceipt;
use App\Services\Framework\Http\EloquentCollectionResponse;

class MealReceiptUniqueSectionCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = MealReceiptUniqueSectionResource::class;

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
        'created_at',
        'updated_at',
    ];
}
