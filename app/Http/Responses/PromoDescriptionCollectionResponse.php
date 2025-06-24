<?php

namespace App\Http\Responses;

use App\Http\Resources\PromoDescriptionResource;
use App\Models\PromoDescription;
use App\Services\Framework\Http\EloquentCollectionResponse;

class PromoDescriptionCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = PromoDescriptionResource::class;

    /**
     * @var string
     */
    protected $model = PromoDescription::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'created_at',
        'is_hidden',
        'discount_type',
    ];
}
