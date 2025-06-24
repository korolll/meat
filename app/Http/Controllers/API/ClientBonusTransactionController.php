<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientBonusTransactionStoreRequest;
use App\Http\Resources\ClientBonusTransactionResource;
use App\Http\Responses\ClientBonusTransactionCollectionResponse;
use App\Models\ClientBonusTransaction;
use App\Services\Management\Client\Bonus\BonusTransactionData;
use App\Services\Management\Client\Bonus\BonusTransactionProducerInterface;

class ClientBonusTransactionController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', ClientBonusTransaction::class);

        return ClientBonusTransactionCollectionResponse::create(
            ClientBonusTransaction::query()
        );
    }

    /**
     * @param \App\Http\Requests\ClientBonusTransactionStoreRequest $request
     *
     * @return \App\Http\Resources\ClientBonusTransactionResource
     * @throws \App\Exceptions\ClientExceptions\ClientDoesNotHaveEnoughBonusBalance
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(ClientBonusTransactionStoreRequest $request)
    {
        $this->authorize('create', ClientBonusTransaction::class);

        $valid = $request->validated();
        $data = BonusTransactionData::create()
            ->setClientId($valid['client_uuid'])
            ->setBonusDelta($valid['delta'])
            ->setReason(ClientBonusTransaction::REASON_MANUAL);

        /** @var BonusTransactionProducerInterface $transactionProducer */
        $transactionProducer = app(BonusTransactionProducerInterface::class);
        $result = $transactionProducer->produce($data);

        return ClientBonusTransactionResource::make($result);
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
