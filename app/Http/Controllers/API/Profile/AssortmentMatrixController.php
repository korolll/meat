<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\QueryFilters\AssortmentPropertyFilterTrait;
use App\Http\Requests\AssortmentMatrixIndexFromSupplierProductRequestRequest;
use App\Http\Requests\AssortmentMatrixStoreRequest;
use App\Http\Responses\AssortmentMatrixCollectionResponse;
use App\Models\Assortment;
use App\Models\Product;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Models\Receipt;
use App\Models\WarehouseTransaction;
use App\Services\Database\VirtualColumns\AssortmentMinPrice;
use App\Services\Management\Product\ByAssortmentProductMaker;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AssortmentMatrixController extends Controller
{
    use AssortmentPropertyFilterTrait;

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('assortment-matrix-index', Assortment::class);

        $assortmentsQuery = $this->user
            ->assortmentMatrix();

        $receiptsOfTheWeek = WarehouseTransaction::query()
            ->select([
                'products.assortment_uuid',
                DB::raw('SUM(warehouse_transactions.quantity_delta) as receipts_of_the_week'),
            ])
            ->leftJoin('products', function (JoinClause $join) {
                $join->on('products.uuid', '=', 'warehouse_transactions.product_uuid');
                $join->on('products.user_uuid', '=', DB::raw("'{$this->user->uuid}'"));
            })
            ->whereBetween('warehouse_transactions.created_at', [
                now()->subWeek()->startOfDay(),
                now()->endOfDay(),
            ])
            ->where('warehouse_transactions.quantity_delta', '>', 0)
            ->whereIn('warehouse_transactions.reference_type', [
                'customer-product-request',
                'supplier-product-request',
            ])
            ->groupBy('products.uuid');

        $offsOfTheWeek = WarehouseTransaction::query()
            ->select([
                'products.assortment_uuid',
                DB::raw('SUM(warehouse_transactions.quantity_delta) as offs_of_the_week'),
            ])
            ->leftJoin('products', function (JoinClause $join) {
                $join->on('products.uuid', '=', 'warehouse_transactions.product_uuid');
                $join->on('products.user_uuid', '=', DB::raw("'{$this->user->uuid}'"));
            })
            ->whereBetween('warehouse_transactions.created_at', [
                now()->subWeek()->startOfDay(),
                now()->endOfDay(),
            ])
            ->whereIn('warehouse_transactions.reference_type', [
                'stocktaking',
                'write-off',
            ])
            ->groupBy('products.uuid');

        $query = $this->makeAssortmentMatrixIndexQuery($request, $assortmentsQuery)
            ->leftJoinSub($receiptsOfTheWeek, 'inner_query2', function (JoinClause $join) {
                $join->on('inner_query2.assortment_uuid', '=', 'assortments.uuid');
            })
            ->addSelect(DB::raw('COALESCE(inner_query2.receipts_of_the_week, 0) as receipts_of_the_week'))
            ->leftJoinSub($offsOfTheWeek, 'inner_query3', function (JoinClause $join) {
                $join->on('inner_query3.assortment_uuid', '=', 'assortments.uuid');
            })
            ->addSelect(DB::raw('COALESCE(inner_query3.offs_of_the_week, 0) as offs_of_the_week'));

        $this->indexAssortmentPropertyFilter($request, $query->getQuery());

        return AssortmentMatrixCollectionResponse::create($query);
    }

    /**
     * @param AssortmentMatrixIndexFromSupplierProductRequestRequest $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function indexFromSupplierRequests(AssortmentMatrixIndexFromSupplierProductRequestRequest $request)
    {
        $this->authorize('assortment-matrix-index', Assortment::class);

        $orderQuantityQuery = Product::query()
            ->select([
                'products.assortment_uuid',
                DB::raw('SUM(product_product_request.quantity) as quantity'),
            ])
            ->join('product_product_request', function (JoinClause $join) use ($request) {
                $uuids = $request->getSupplierProductRequestUuids();

                $join->on('product_product_request.product_uuid', '=', 'products.uuid');
                $join->whereIn('product_product_request.product_request_uuid', $uuids);
            })
            ->groupBy('products.assortment_uuid');

        $assortmentsQuery = Assortment::query()
            ->select([
                'assortments.*',
                'order_quantity_query.quantity as order_quantity',
            ])
            ->joinSub($orderQuantityQuery, 'order_quantity_query', function (JoinClause $join) {
                $join->on('order_quantity_query.assortment_uuid', '=', 'assortments.uuid');
            });

        return AssortmentMatrixCollectionResponse::create(
            $this->makeAssortmentMatrixIndexQuery($request, $assortmentsQuery)
        );
    }

    /**
     * @param AssortmentMatrixStoreRequest $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function store(AssortmentMatrixStoreRequest $request)
    {
        if ($request->exists('*RequestFilters')) {
            return $this->index($request);
        }
        $assortment = Assortment::findOrFail($request->assortment_uuid);

        $this->authorize('assortment-matrix-attach', $assortment);

        $result = $this->user->assortmentMatrix()->syncWithoutDetaching($assortment);
        if ($result['attached']) {
            app(ByAssortmentProductMaker::class)->make($this->user, $assortment);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Assortment $assortmentMatrix
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Assortment $assortmentMatrix)
    {
        $this->authorize('assortment-matrix-detach', $assortmentMatrix);

        $count = $this->user->assortmentMatrix()->detach($assortmentMatrix);

        return response('', $count ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }

    /**
     * @param Request $request
     * @param \Illuminate\Database\Eloquent\Builder|mixed $assortmentsQuery
     * @return \Illuminate\Database\Eloquent\Builder|mixed
     */
    private function makeAssortmentMatrixIndexQuery(Request $request, $assortmentsQuery)
    {
        // Для подсчета кол-ва продаж за неделю
        $weekSalesQuery = WarehouseTransaction::query()
            ->select([
                'products.assortment_uuid',
                DB::raw('-1 * SUM(warehouse_transactions.quantity_delta) as week_sales'),
            ])
            ->leftJoin('products', function (JoinClause $join) {
                $join->on('products.uuid', '=', 'warehouse_transactions.product_uuid');
                $join->on('products.user_uuid', '=', DB::raw("'{$this->user->uuid}'"));
            })
            ->whereBetween('warehouse_transactions.created_at', [
                now()->subWeek()->startOfDay(),
                now()->endOfDay(),
            ])
            ->whereIn('warehouse_transactions.reference_type', [
                Receipt::MORPH_TYPE_ALIAS,
                SupplierProductRequest::MORPH_TYPE_ALIAS,
            ])
            ->groupBy('products.uuid');

        // Полный запрос: матрица, остатки, продажи за неделю
        $assortmentMatrixQuery = $assortmentsQuery
            ->addSelect([
                DB::raw('COALESCE(products.quantity, 0) as quantity'),
                DB::raw('COALESCE(inner_query.week_sales, 0) as week_sales'),
            ])
            ->leftJoin('products', function (JoinClause $join) {
                $join->on('products.assortment_uuid', '=', 'assortments.uuid');
                $join->on('products.user_uuid', '=', DB::raw("'{$this->user->uuid}'"));
            })
            ->leftJoinSub($weekSalesQuery, 'inner_query', function (JoinClause $join) {
                $join->on('inner_query.assortment_uuid', '=', 'assortments.uuid');
            });

        // Добавляем минимальные цены
        $priceMinSelect = 'COALESCE(inner_query_min_price.price_min, inner_query_min_price_global.price_min, 0) as price_min';
        $assortmentMatrixQuery->addSelect(DB::raw($priceMinSelect))
            ->addVirtualColumn(AssortmentMinPrice::class, 'inner_query_min_price', [
                null,
                $request->get('min_price_user_uuids'),
                $this->user->uuid
            ])->addVirtualColumn(AssortmentMinPrice::class, 'inner_query_min_price_global', [
                null,
                $request->get('min_price_user_uuids'),
                null
            ]);

        $priceMin = $request->price_min;
        if ($priceMin && is_array($priceMin)) {
            $priceMinOperator = @$priceMin['operator'];
            $operatorsArray = ['>', '>=', '<', '<=', '=', '!='];
            if (!$priceMinOperator || !in_array($priceMinOperator, $operatorsArray, true)) {
                throw new BadRequestHttpException('Valid characters for comparison parameter, price_min: ' . implode(',', $operatorsArray));
            }
            $priceMinValue = @$priceMin['value'];
            if ($priceMinValue === null) {
                throw new BadRequestHttpException('Parameter "price_min" must be have key "value" with type "float"');
            }
            $whereRaw = "COALESCE(inner_query_min_price.price_min, inner_query_min_price_global.price_min, 0) {$priceMinOperator} ?";
            $assortmentMatrixQuery->whereRaw($whereRaw, [(float) $priceMinValue]);
        }

        return $assortmentMatrixQuery;
    }
}
