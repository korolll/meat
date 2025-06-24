<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\ProfileUpdateRequest;
use App\Http\Requests\Clients\API\PurchasesSumRequest;
use App\Http\Resources\Clients\API\ProfileResource;
use App\Http\Responses\PurchaseLineResponse;
use App\Models\Client;
use App\Models\OrderStatus;
use App\Services\Database\Table\DiscountForbiddenCatalogRecursiveTable;
use App\Services\Framework\Http\CollectionRequest;
use App\Services\Money\MoneyHelper;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ProfileController extends Controller
{
    /**
     * @return mixed
     */
    public function show()
    {
        $client = $this->client;
        $client->touch();

        return ProfileResource::make($client);
    }

    /**
     * @param ProfileUpdateRequest $request
     *
     * @return mixed
     * @throws \Throwable
     */
    public function update(ProfileUpdateRequest $request)
    {
        $client = DB::transaction(function () use ( $request) {
            $lockedClient = Client::lockForUpdate()
                ->where('uuid', $this->client->uuid)
                ->first();

            $lockedClient->fill($request->validated());
            $lockedClient->saveOrFail();

            return $lockedClient;
        });

        return ProfileResource::make($client);
    }

    /**
     * @param \App\Http\Requests\Clients\API\PurchasesSumRequest $request
     *
     * @return array
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function purchasesSum(PurchasesSumRequest $request)
    {
        $client = $this->client;
        $numOfDays = $request->validated()['days'];
        $day = Date::now()->subDays($numOfDays);

        $sumOfOrders = $client->orders()
            ->where('updated_at', '>=', $day)
            ->where('order_status_id', OrderStatus::ID_DONE)
            ->sum('total_price_for_products_with_discount');

        $sumOfReceipts = $client->receipts()
            ->where('receipts.created_at', '>=', $day)
            ->whereNull('receipts.refund_by_receipt_uuid')
            ->sum('receipts.total');

        $total = MoneyHelper::of($sumOfOrders)->plus($sumOfReceipts);
        $total = MoneyHelper::toFloat($total);

        return [
            'data' => $total
        ];
    }

    /**
     * @param \App\Services\Framework\Http\CollectionRequest $request
     *
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function purchasesMonth(CollectionRequest $request)
    {
        $client = $this->client;
        $now = now();

        $bannedCatalogsTable = new DiscountForbiddenCatalogRecursiveTable();
        $bannedQuery = $bannedCatalogsTable->table('banned_catalogs');

        $query = $client->purchasesView()
            ->join('products', 'products.uuid', '=', 'purchases_view.product_uuid')
            ->join('assortments', 'assortments.uuid', '=', 'products.assortment_uuid')
            ->joinSub($bannedQuery, 'bc', 'bc.catalog_uuid', '=', 'assortments.catalog_uuid', 'left')
            ->whereNull('bc.catalog_uuid')
            ->leftJoin('discount_forbidden_assortments', 'discount_forbidden_assortments.assortment_uuid', '=', 'assortments.uuid')
            ->whereNull('discount_forbidden_assortments.assortment_uuid')
            ->distinct('products.assortment_uuid')
            ->whereBetween('purchases_view.created_at', [
                $now->startOfMonth(),
                $now->endOfMonth()
            ]);

        return PurchaseLineResponse::create($query);
    }

    public function delete(Request $request): JsonResponse
    {
        $client = $this->client;

        /** @var Сlient $client */
        if (!$client->mark_deleted_at) {
            $new_mark = true;
            $client->mark_deleted_at = Carbon::now();
            $client->save();
        } else {
            $new_mark = false;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'new_mark' => $new_mark,
                'mark_deleted_at' => $client->mark_deleted_at,
                ],
            ]);
        }
}
