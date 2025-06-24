<?php

namespace App\Http\Responses;

use App\Http\Resources\ClientBonusTransactionResource;
use App\Models\ClientBonusTransaction;
use App\Services\Framework\Http\EloquentCollectionResponse;

class ClientBonusTransactionCollectionResponse extends EloquentCollectionResponse
{
    /**
     * @var string
     */
    protected $resource = ClientBonusTransactionResource::class;

    /**
     * @var string
     */
    protected $model = ClientBonusTransaction::class;

    /**
     * @var array
     */
    protected $attributes = [
        'client_uuid',
        'related_reference_id',
        'related_reference_type',
        'reason',
        'quantity_old',
        'quantity_delta',
        'quantity_new',
        'created_at',
    ];
}
