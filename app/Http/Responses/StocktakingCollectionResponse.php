<?php

namespace App\Http\Responses;

use App\Http\Resources\StocktakingResource;
use App\Models\Stocktaking;
use App\Services\Framework\Http\EloquentCollectionResponse;

class StocktakingCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = StocktakingResource::class;

    /**
     * @var string
     */
    protected $model = Stocktaking::class;

    /**
     * @var array
     */
    protected $attributes = [
        'approved_at',
        'created_at',
    ];
}
