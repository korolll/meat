<?php

namespace App\Http\Responses;

use App\Http\Resources\PromoDiverseFoodSettingsResource;
use App\Models\PromoDiverseFoodSettings;
use App\Services\Framework\Http\EloquentCollectionResponse;


class PromoDiverseFoodSettingsCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = PromoDiverseFoodSettingsResource::class;

    /**
     * @var string
     */
    protected $model = PromoDiverseFoodSettings::class;

    /**
     * @var array
     */
    protected $attributes = [
        'uuid',
        'count_purchases',
        'count_rating_scores',
        'discount_percent',
        'is_enabled',
    ];
}
