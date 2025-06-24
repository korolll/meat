<?php

namespace App\Http\Responses;

use App\Http\Resources\UserResourceCollection;
use App\Models\User;
use App\Services\Framework\Http\EloquentCollectionResponse;

class UserCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = UserResourceCollection::class;

    /**
     * @var string
     */
    protected $model = User::class;

    /**
     * @var array
     */
    protected $attributes = [
        'organization_name',
        'address',
        'inn',
        'kpp',
        'user_verify_status_id',
        'created_at',
        'user_type_id',
    ];
}
