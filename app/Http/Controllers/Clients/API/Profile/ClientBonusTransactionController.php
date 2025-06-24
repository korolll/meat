<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientBonusTransactionResource;
use App\Http\Responses\ClientBonusTransactionCollectionResponse;
use App\Models\ClientBonusTransaction;

class ClientBonusTransactionController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', ClientBonusTransaction::class);

        return ClientBonusTransactionCollectionResponse::create(
            ClientBonusTransaction::where('client_uuid', $this->client->uuid)
        );
    }

    /**
     * @param \App\Models\ClientBonusTransaction $clientBonusTransaction
     *
     * @return \App\Http\Resources\ClientBonusTransactionResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ClientBonusTransaction $clientBonusTransaction)
    {
        $this->authorize('view', $clientBonusTransaction);

        return ClientBonusTransactionResource::make($clientBonusTransaction);
    }
}
