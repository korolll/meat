<?php

namespace App\Http\Controllers\API\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchasesReportRequest;
use App\Http\Resources\API\Reports\ActionsPurchasesReportResource;
use App\Http\Resources\API\Reports\PurchasesReportResource;
use App\Models\Assortment;
use App\Models\PurchaseView;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class PurchasesReportController extends Controller
{
    /**
     * @param \App\Http\Requests\PurchasesReportRequest $request
     *
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function report(PurchasesReportRequest $request)
    {
        $this->authorize('report', PurchaseView::class);

        $limit = $request->limit;
        if ($limit && is_numeric($limit)) {
            $limit = (int)$limit;
        }

        if ($limit <= 0 || $limit > 50) {
            $limit = 15;
        }

        $subQuery = $this->getBaseQuery($request)
            ->join('products', 'products.uuid', '=', 'purchases_view.product_uuid')
            ->groupBy('products.assortment_uuid')
            ->select([
                'products.assortment_uuid',
                DB::raw('SUM(purchases_view.total_amount_with_discount) as total_sum'),
                DB::raw('SUM(purchases_view.quantity) as total_quantity'),
            ]);

        $result = DB::query()->fromSub($subQuery, 'sub_purchases')
            ->select('*')
            ->orderBy('total_sum', 'DESC')
            ->limit($limit)
            ->get();

        $assortments = Assortment::query()
            ->whereIn('uuid', $result->pluck('assortment_uuid'))
            ->get()
            ->keyBy('uuid');

        $finalArr = new Collection();
        foreach ($result as $assortmentResult) {
            $assortment = $assortments[$assortmentResult->assortment_uuid];
            $assortment->total_sum = $assortmentResult->total_sum;
            $assortment->total_quantity = $assortmentResult->total_quantity;
            $finalArr[] = $assortment;
        }

        return PurchasesReportResource::collection($finalArr);
    }

    /**
     * @param \App\Http\Requests\PurchasesReportRequest $request
     *
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function actionsReport(PurchasesReportRequest $request)
    {
        $this->authorize('actions-report', PurchaseView::class);

        $result = $this->getBaseQuery($request)
            ->whereNotNull('discountable_type')
            ->groupBy('discountable_type')
            ->select([
                'discountable_type',
                DB::raw('SUM(total_amount_with_discount) as total_sum'),
                DB::raw('SUM(quantity) as total_quantity'),
            ])
            ->orderBy('discountable_type')
            ->get();

        return ActionsPurchasesReportResource::collection($result);
    }

    /**
     * @param \App\Http\Requests\PurchasesReportRequest $request
     *
     * @return \App\Models\PurchaseView|\Illuminate\Database\Eloquent\Builder
     */
    protected function getBaseQuery(PurchasesReportRequest $request)
    {
        $dateStart = Date::parse($request->date_start);
        $dateEnd = Date::parse($request->date_end);

        $userUuid = $this->getTargetUserUuid($request);
        $query = PurchaseView::query()
            ->where('bought_at', '>=', $dateStart)
            ->where('bought_at', '<=', $dateEnd);

        if ($userUuid) {
            $query = $query->where('store_user_uuid', $userUuid);
        }

        return $query;
    }

    /**
     * @param \App\Http\Requests\PurchasesReportRequest $request
     *
     * @return string|null
     */
    protected function getTargetUserUuid(PurchasesReportRequest $request): ?string
    {
        $user = $this->user;
        if (! $user->is_admin) {
            return $user->uuid;
        }

        return $request->store_uuid;
    }
}
