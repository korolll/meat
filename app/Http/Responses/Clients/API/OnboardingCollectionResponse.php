<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\OnboardingResource;
use App\Models\Onboarding;
use App\Services\Framework\Http\EloquentCollectionResponse;

class OnboardingCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = OnboardingResource::class;

    /**
     * @var string
     */
    protected $model = Onboarding::class;

    /**
     * @var array
     */
    protected $attributes = [
        'title',
        'sort_number',
        'created_at',
    ];
}
