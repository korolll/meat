<?php

namespace App\Http\Responses\Clients\API\Profile;

use App\Http\Resources\Clients\API\Profile\ReceiptResource;
use App\Models\Receipt;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ReceiptCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ReceiptResource::class;

    /**
     * @var string
     */
    protected $model = Receipt::class;

    /**
     * @var array
     */
    protected $attributes = [
        'id',
        'total',
        'created_at',
        'refund_by_receipt_uuid',
    ];
}
