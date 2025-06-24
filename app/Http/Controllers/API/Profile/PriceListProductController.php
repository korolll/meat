<?php

namespace App\Http\Controllers\API\Profile;

use App\Exceptions\TealsyException;
use App\Http\Controllers\Controller;
use App\Http\QueryFilters\AssortmentPropertyFilterTrait;
use App\Http\Requests\PriceListProductBatchUpdateRequest;
use App\Http\Requests\PriceListProductUpdateRequest;
use App\Http\Responses\PriceListProductCollectionResponse;
use App\Models\PriceList;
use App\Services\Management\PriceList\ProductManagerContract;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PriceListProductController extends Controller
{
    use AssortmentPropertyFilterTrait;

    /**
     * @param PriceList $priceList
     * @param Request $request
     * @return mixed
     * @throws TealsyException
     * @throws AuthorizationException
     */
    public function index(PriceList $priceList, Request $request)
    {
        $this->authorize('view', $priceList);

        $query = $priceList->products()->with(['assortment.tags']);

        $this->indexAssortmentPropertyFilter($request, $query->getQuery(), true);

        return PriceListProductCollectionResponse::create($query);
    }

    /**
     * @param PriceListProductUpdateRequest $request
     * @param PriceList $priceList
     * @param string $productUuid
     * @return mixed
     * @throws AuthorizationException
     */
    public function update(PriceListProductUpdateRequest $request, PriceList $priceList, $productUuid)
    {
        $this->authorize('update', $priceList);

        $count = $priceList->products()->updateExistingPivot($productUuid, [
            'price_new' => $request->price_new,
        ]);

        return response('', $count ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }

    /**
     * @param PriceListProductUpdateRequest $request
     * @param PriceList $priceList
     * @param string $productUuid
     * @return mixed
     * @throws AuthorizationException
     * @throws Exception
     */
    public function batchUpdate(PriceListProductBatchUpdateRequest $request, PriceList $priceList)
    {
        $this->authorize('update', $priceList);

        $priceList->load('products');
        $errorsUuids = [];
        $errorMessage = '';
        DB::beginTransaction();
        foreach ($request->products as $productData) {
            $count = $priceList->products()->updateExistingPivot($productData['product_uuid'], [
                'price_new' => $productData['price_new'],
            ]);
            if (!$count) {
                $errorsUuids[] = $productData['product_uuid'];
                DB::rollBack();
                break;
            }
        }
        if (!$errorsUuids) {
            DB::commit();
        } else {
            $errorMessage = 'Products not found with uuids:' . implode(', ', $errorsUuids);
        }

        return response($errorMessage, $errorsUuids ? Response::HTTP_NOT_FOUND : Response::HTTP_OK);
    }

    /**
     * @param PriceList $priceList
     * @return mixed
     */
    public function synchronize(PriceList $priceList)
    {
        $count = app(ProductManagerContract::class)->synchronize($priceList);

        return ['data' => ['synchronized' => $count]];
    }
}
