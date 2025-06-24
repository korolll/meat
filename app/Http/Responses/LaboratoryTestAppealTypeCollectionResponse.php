<?php

namespace App\Http\Responses;

use App\Http\Resources\LaboratoryTestAppealTypeResource;
use App\Models\LaboratoryTestAppealType;
use App\Services\Framework\Http\EloquentCollectionResponse;

class LaboratoryTestAppealTypeCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = LaboratoryTestAppealTypeResource::class;

    /**
     * @var string
     */
    protected $model = LaboratoryTestAppealType::class;

    /**
     * @var array
     */
    protected $attributes = [
        'name',
        'created_at',
    ];
}
