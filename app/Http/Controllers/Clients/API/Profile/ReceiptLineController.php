<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\ReceiptLineSetRatingRequest;
use App\Http\Resources\Clients\API\Profile\ReceiptLineResource;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use Illuminate\Http\Response;

class ReceiptLineController extends Controller
{
    /**
     * @param Receipt $receipt
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Receipt $receipt)
    {
        $this->authorize('view', $receipt);

        return ReceiptLineResource::collection(
            $receipt->receiptLines
        );
    }

    /**
     * @param ReceiptLineSetRatingRequest $request
     * @param Receipt $receipt
     * @param ReceiptLine $receiptLine
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setRating(ReceiptLineSetRatingRequest $request, Receipt $receipt, ReceiptLine $receiptLine)
    {
        $this->authorize('set-rating', [$receipt, $receiptLine]);

        $ratingScoreFactory = app('factory.rating-score.receipt-line');
        $ratingScoreFactory->create($receiptLine->assortment, $this->client, $receiptLine, $request->value, [
            'comment' => $request->comment,
        ]);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
