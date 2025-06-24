<?php

namespace App\Http\Responses;

use App\Http\Resources\ReceiptResource;
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
        'loyalty_card_uuid',
        'client_uuid',
    ];

    /**
     * @var string[]
     */
    protected $attributeMappings = [
        'client_uuid' => 'loyaltyCard.client_uuid'
    ];
}
