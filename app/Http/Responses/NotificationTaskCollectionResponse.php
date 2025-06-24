<?php

namespace App\Http\Responses;

use App\Http\Resources\NotificationTaskResource;
use App\Models\NotificationTask;
use App\Services\Framework\Http\EloquentCollectionResponse;

class NotificationTaskCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = NotificationTaskResource::class;

    /**
     * @var string
     */
    protected $model = NotificationTask::class;

    /**
     * @var array
     */
    protected $attributes = [
        'execute_at',
        'taken_to_work_at',
        'executed_at',
        'created_at',
        'updated_at',
    ];
}
