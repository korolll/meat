<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Clients\API\Profile\ReceiptLineResource;
use App\Http\Resources\ReceiptResource;
use App\Http\Responses\ReceiptCollectionResponse;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Services\Framework\Http\CollectionRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
    /**
     * @param \App\Services\Framework\Http\CollectionRequest $request
     *
     * @return \App\Http\Responses\ReceiptCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(CollectionRequest $request)
    {
        $this->authorize('index', Receipt::class);

        $call = function (LengthAwarePaginator $paginator) {
            $this->loadAggregationsData($paginator);
        };

        $response = new ReceiptCollectionResponse(
            $request,
            Receipt::query()
        );
        $response->setBeforeToResource($call);
        return $response;
    }

    /**
     * @param \App\Models\Receipt $receipt
     *
     * @return \App\Http\Resources\ReceiptResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Receipt $receipt)
    {
        $this->authorize('view', $receipt);
        $this->loadAggregationsData([$receipt]);

        return ReceiptResource::make($receipt);
    }

    /**
     * @param \App\Models\Receipt $receipt
     *
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function lines(Receipt $receipt)
    {
        $this->authorize('view', $receipt);

        return ReceiptLineResource::collection(
            $receipt->receiptLines
        );
    }

    /**
     * @param iterable<Receipt> $receipts
     *
     * @return void
     */
    protected function loadAggregationsData(iterable $receipts)
    {
        $map = [];
        $uuids = [];
        foreach ($receipts as $receipt) {
            $uid = $receipt->uuid;
            $uuids[] = $uid;
            $map[$uid] = $receipt;
        }

        $result = ReceiptLine::query()
            ->whereIn('receipt_uuid', $uuids)
            ->leftJoin('products', 'products.uuid', 'receipt_lines.product_uuid')
            ->leftJoin('assortments', 'assortments.uuid', 'products.assortment_uuid')
            ->select([
                'receipt_lines.receipt_uuid',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(assortments.weight * receipt_lines.quantity) as total_weight'),
                DB::raw('SUM(receipt_lines.discount * receipt_lines.quantity) as total_discount'),
            ])
            ->groupBy('receipt_uuid')
            ->toBase()
            ->get();

        foreach ($result as $row) {
            /** @var Receipt $receipt */
            $receipt = $map[$row->receipt_uuid];
            $receipt->receipt_lines_count = $row->count;
            $receipt->receipt_lines_total_discount = $row->total_discount;
            $receipt->receipt_lines_total_weight = $row->total_weight;
        }
    }
}
