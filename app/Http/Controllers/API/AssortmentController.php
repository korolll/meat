<?php

namespace App\Http\Controllers\API;

use App\Contracts\Models\Assortment\SaveAssortmentContract;
use App\Contracts\Models\Product\MakeProductsAvailableForRequestQueryContract;
use App\Http\Controllers\Controller;
use App\Http\QueryFilters\AssortmentPropertyFilterTrait;
use App\Http\Requests\AssortmentFindByBarcodeRequest;
use App\Http\Requests\AssortmentFindProductsRequest;
use App\Http\Requests\AssortmentStoreRequest;
use App\Http\Requests\AssortmentVerifyRequest;
use App\Http\Resources\AssortmentResource;
use App\Http\Responses\AssortmentCollectionResponse;
use App\Http\Responses\AssortmentProductCollectionResponse;
use App\Models\Assortment;
use App\Models\AssortmentBarcode;
use App\Models\AssortmentVerifyStatus;
use App\Services\Database\VirtualColumns\AssortmentExistsInAssortmentMatrix;
use App\Services\Database\VirtualColumns\AssortmentMinPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Database\Query\JoinClause;

class AssortmentController extends Controller
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
        $this->authorize('index', Assortment::class);

        $query = Assortment::with(['assortmentProperties', 'files', 'tags'])
            ->select('assortments.*');

        $this->indexPriceMinFilter($request, $query);
        $this->indexAssortmentPropertyFilter($request, $query);
        
        $storeUuid = $request->get('store_uuid');

        if ($storeUuid) {
            $query
                ->leftJoin('products', function (JoinClause $join) use ($storeUuid) {
                    $join->on('products.assortment_uuid', '=', 'assortments.uuid');
                    $join->where('products.user_uuid', '=', $storeUuid);
                })
                ->addSelect('products.quantity as products_quantity')
                ->addSelect('products.price as current_price');
        }

        return AssortmentCollectionResponse::create($query);
    }

    /**
     * @param Request $request
     * @param Builder $query
     */
    protected function indexPriceMinFilter(Request $request, Builder $query)
    {
        $minPriceUserUuids = $request->exists('min_price_user_uuids') ? explode(',', $request->min_price_user_uuids) : null;

        // Добавляем минимальные цены
        $priceMinSelect = 'COALESCE(inner_query_min_price.price_min, inner_query_min_price_global.price_min, 0) as price_min';

        $query->addSelect(DB::raw($priceMinSelect))
            ->addVirtualColumn(AssortmentMinPrice::class, 'inner_query_min_price', [
                $minPriceUserUuids,
                $request->get('min_price_user_uuids'),
                $this->user->uuid
            ])->addVirtualColumn(AssortmentMinPrice::class, 'inner_query_min_price_global', [
                $minPriceUserUuids,
                $request->get('min_price_user_uuids'),
                null
            ])
            ->addVirtualColumn(AssortmentExistsInAssortmentMatrix::class, 'is_exists_in_assortment_matrix', [
                $this->user->uuid
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
            $query->whereRaw($whereRaw, [(float) $priceMinValue]);
        }

        if ($request->exists('is_exists_in_assortment_matrix')) {
            if ($request->is_exists_in_assortment_matrix === 'true') {
                $query->whereNotNull('assortment_matrices.user_uuid');
            } else {
                $query->whereNull('assortment_matrices.user_uuid');
            }
        }
    }

    /**
     * @param AssortmentStoreRequest $request
     * @param SaveAssortmentContract $saver
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(AssortmentStoreRequest $request, SaveAssortmentContract $saver)
    {
        if ($request->exists('*RequestFilters')) {
            return $this->index($request);
        }
        /**
         * @var $assortment Assortment
         * @var $assortmentBarcode AssortmentBarcode
         */
        $assortmentBarcode = AssortmentBarcode::whereIn('assortment_barcodes.barcode', $request->barcodes)->first();
        $assortment = $assortmentBarcode ? $assortmentBarcode->assortment : new Assortment();

        if (!$assortment->exists) {
            $this->authorize('create', Assortment::class);
        } else {
            $this->authorize('update', $assortment);
        }

        $assortment->assortment_verify_status_id = AssortmentVerifyStatus::ID_APPROVED;
        $assortment = $saver->save($assortment, $request->asSaveData());

        return AssortmentResource::make($assortment);
    }

    /**
     * @param Assortment $assortment
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Assortment $assortment)
    {
        $this->authorize('view', $assortment);

        return AssortmentResource::make($assortment);
    }

    /**
     * @param AssortmentStoreRequest $request
     * @param Assortment $assortment
     * @param SaveAssortmentContract $saver
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(AssortmentStoreRequest $request, Assortment $assortment, SaveAssortmentContract $saver)
    {
        $this->authorize('update', $assortment);

        $saver->save($assortment, $request->asSaveData());

        return AssortmentResource::make($assortment);
    }

    /**
     * @param AssortmentFindByBarcodeRequest $request
     * @return AssortmentResource
     */
    public function findByBarcode(AssortmentFindByBarcodeRequest $request)
    {
        $assortment = Assortment::approved()
            ->join('assortment_barcodes', 'assortment_barcodes.assortment_uuid', '=', 'assortments.uuid')
            ->where('assortment_barcodes.barcode', '=', $request->barcode)
            ->firstOrFail();

        return AssortmentResource::make($assortment);
    }

    /**
     * @param Assortment $assortment
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function products(Assortment $assortment)
    {
        $this->authorize('view', $assortment);

        return AssortmentProductCollectionResponse::create(
            resolve(MakeProductsAvailableForRequestQueryContract::class)->make([
                'customer_user_uuid' => $this->user->uuid,
                'assortment_uuids' => [$assortment->uuid]
            ])
        );
    }

    /**
     * @param AssortmentFindProductsRequest $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    public function findProducts(AssortmentFindProductsRequest $request)
    {
        return AssortmentProductCollectionResponse::create(
            resolve(MakeProductsAvailableForRequestQueryContract::class)->make([
                'customer_user_uuid' => $this->user->uuid,
                'assortment_uuids' => $request->assortment_uuids
            ])
        );
    }

    /**
     * @param AssortmentVerifyRequest $request
     * @param Assortment $assortment
     * @return mixed
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function verify(AssortmentVerifyRequest $request, Assortment $assortment)
    {
        $this->authorize('verify', $assortment);

        $assortment->assortment_verify_status_id = $request->assortment_verify_status_id;
        $assortment->saveOrFail();

        return AssortmentResource::make($assortment);
    }
}
