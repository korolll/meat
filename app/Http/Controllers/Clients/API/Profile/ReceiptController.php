<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Clients\API\Profile\ReceiptResource;
use App\Http\Responses\Clients\API\Profile\ReceiptCollectionResponse;
use App\Models\Receipt;

class ReceiptController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', Receipt::class);

        return ReceiptCollectionResponse::create(
            $this->client->receipts()->withCount('receiptLines')
        );
    }

    /**
     * @param Receipt $receipt
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Receipt $receipt)
    {
        $this->authorize('view', $receipt);

        return ReceiptResource::make($receipt);
    }
}
