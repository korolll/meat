<?php

namespace App\Http\Responses\Clients\API;

use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use App\Services\Framework\Http\EloquentCollectionResponse;

class VacancyCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = VacancyResource::class;

    /**
     * @var string
     */
    protected $model = Vacancy::class;

    /**
     * @var array
     */
    protected $attributes = [
        'title',
        'sort_number',
        'created_at',
    ];
}
