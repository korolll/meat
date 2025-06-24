<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\Clients\API\Profile\PromoDiverseFoodClientStatResource;
use App\Models\PromoDiverseFoodClientStat;
use App\Services\Framework\Http\EloquentCollectionResponse;

class PromoDiverseFoodClientStatResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = PromoDiverseFoodClientStatResource::class;

    /**
     * @var string
     */
    protected $model = PromoDiverseFoodClientStat::class;

    /**
     * @var array
     */
    protected $attributes = [
        'month',
        'purchased_count',
        'rated_count',
        'client_uuid',
        'updated_at',
        'created_at',
    ];
}
