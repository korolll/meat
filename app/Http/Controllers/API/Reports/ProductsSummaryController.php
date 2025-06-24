<?php

namespace App\Http\Controllers\API\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportProductsSummaryRequest;
use App\Http\Responses\ReportProductsSummaryCollectionResponse;
use App\Http\Responses\ReportProductsSummaryTransactionCollectionResponse;
use App\Models\Product;
use App\Models\WarehouseTransaction;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ProductsSummaryController extends Controller
{
    /**
     * @param ReportProductsSummaryRequest $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(ReportProductsSummaryRequest $request)
    {
        $this->authorize('report-products-summary-index', Product::class);

        $dateStart = Date::parse($request->date_start);
        $dateEnd = Date::parse($request->date_end);

        $innerQuery = $this->user->products()
            ->select([
                // ИД Продукта
                'products.uuid',
                // Количество операций на списание
                DB::raw('SUM(CASE WHEN warehouse_transactions.quantity_delta < 0 THEN warehouse_transactions.quantity_delta ELSE 0 END) as delta_minus'),
                // Количество операций на пополнение
                DB::raw('SUM(CASE WHEN warehouse_transactions.quantity_delta > 0 THEN warehouse_transactions.quantity_delta ELSE 0 END) as delta_plus'),
                // Остаток на начало периода
                DB::raw('find_product_quantity_in_timestamp(products.uuid, ?) as quantity_on_start'),
                // Остаток на конец периода
                DB::raw('find_product_quantity_in_timestamp(products.uuid, ?) as quantity_on_end'),
            ])
            ->addBinding($dateStart, 'select')
            ->addBinding($dateEnd, 'select')
            ->leftJoin('warehouse_transactions', function (JoinClause $join) use ($dateStart, $dateEnd) {
                $join->on('warehouse_transactions.product_uuid', '=', 'products.uuid')
                    ->on('warehouse_transactions.created_at', '>=', DB::raw('?'))
                    ->on('warehouse_transactions.created_at', '<=', DB::raw('?'))
                    ->addBinding($dateStart, 'join')
                    ->addBinding($dateEnd, 'join');
            })
            ->groupBy('products.uuid')
            ->getBaseQuery();

        $query = Product::select([
            // Данные продукта
            'products.*',
            // Данные подзапроса
            'inner_query.delta_minus',
            'inner_query.delta_plus',
            'inner_query.quantity_on_start',
            'inner_query.quantity_on_end',
        ]);

        $query->joinSub($innerQuery, 'inner_query', 'inner_query.uuid', '=', 'products.uuid');

        return ReportProductsSummaryCollectionResponse::create($query);
    }

    /**
     * @param ReportProductsSummaryRequest $request
     * @param Product $product
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ReportProductsSummaryRequest $request, Product $product)
    {
        $this->authorize('report-products-summary-view', $product);

        $dateStart = Date::parse($request->date_start);
        $dateEnd = Date::parse($request->date_end);

        $query = WarehouseTransaction::select([
            // ИД транзакции
            'warehouse_transactions.uuid',
            // Дата транзакции
            'warehouse_transactions.created_at',
            // Дельта транзакции
            'warehouse_transactions.quantity_delta',
            // Вид транзакции
            'warehouse_transactions.reference_type',
        ]);

        $query->where('warehouse_transactions.product_uuid', $product->uuid);
        $query->whereBetween('warehouse_transactions.created_at', [$dateStart, $dateEnd]);

        return ReportProductsSummaryTransactionCollectionResponse::create($query);
    }
}
