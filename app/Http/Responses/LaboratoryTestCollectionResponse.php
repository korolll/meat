<?php

namespace App\Http\Responses;

use App\Http\Resources\LaboratoryTestCollectionResource;
use App\Models\LaboratoryTest;
use App\Services\Framework\Http\EloquentCollectionResponse;

class LaboratoryTestCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = LaboratoryTestCollectionResource::class;

    /**
     * @var string
     */
    protected $model = LaboratoryTest::class;

    /**
     * @var array
     */
    protected $attributes = [
        'customer_full_name',
        'customer_user_uuid',
        'laboratory_test_status_id',
        'created_at',
    ];
}
