<?php

namespace App\Http\Responses;

use App\Http\Resources\RatingScoreResource;
use App\Models\RatingScore;
use App\Services\Framework\Http\EloquentCollectionResponse;

class RatingScoreForAssortmentCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = RatingScoreResource::class;

    /**
     * @var string
     */
    protected $model = RatingScore::class;

    /**
     * @var array
     */
    protected $attributes = [
        'assortment_uuid',
        'client_uuid',
        'value',
        'created_at',
        'comment',
    ];

    /**
     * @var array
     */
    protected $attributeMappings = [
        'assortment_uuid' => 'assortments.uuid',
        'client_uuid' => 'clients.uuid',
        'comment' => 'additional_attributes->comment',
    ];
}
