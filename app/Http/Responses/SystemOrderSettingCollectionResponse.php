<?php

namespace App\Http\Responses;

use App\Http\Resources\SystemOrderSettingResource;
use App\Models\SystemOrderSetting;
use App\Services\Framework\Http\EloquentCollectionResponse;

class SystemOrderSettingCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = SystemOrderSettingResource::class;

    /**
     * @var string
     */
    protected $model = SystemOrderSetting::class;

    /**
     * @var array
     */
    protected $attributes = [
        'id',
        'value',
        'created_at',
        'updated_at',
    ];
}
