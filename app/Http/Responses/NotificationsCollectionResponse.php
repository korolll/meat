<?php

namespace App\Http\Responses;

use App\Http\Resources\NotificationResource;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = NotificationResource::class;

    /**
     * @var string
     */
    protected $model = DatabaseNotification::class;

    /**
     * @var array
     */
    protected $attributes = [
        'id',
        'type',
        'created_at',
        'read_at',
        'deleted_at',

    ];
}
