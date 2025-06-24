<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\QueryFilters\AssortmentPropertyFilterTrait;
use App\Http\Requests\StocktakingProductBatchUpdateRequest;
use App\Http\Requests\StocktakingProductUpdateRequest;
use App\Http\Responses\StocktakingProductCollectionResponse;
use App\Models\Stocktaking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StocktakingProductController extends Controller
{
    use AssortmentPropertyFilterTrait;

    /**
     * @param Stocktaking $stocktaking
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Stocktaking $stocktaking, Request $request)
    {
        $this->authorize('view', $stocktaking);

        $query = $stocktaking->products();

        $this->indexAssortmentPropertyFilter($request, $query->getQuery(), true);

        return StocktakingProductCollectionResponse::create($query);
    }

    /**
     * @param Stocktaking $stocktaking
     * @param Request $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Stocktaking $stocktaking, Request $request)
    {
        if ($request->exists('*RequestFilters')) {
            return $this->index($stocktaking, $request);
        }

        return null;
    }

    /**
     * @param \App\Http\Requests\StocktakingProductUpdateRequest $request
     * @param \App\Models\Stocktaking                            $stocktaking
     * @param                                                    $productUuid
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(StocktakingProductUpdateRequest $request, Stocktaking $stocktaking, $productUuid)
    {
        $this->authorize('update', $stocktaking);
        return $this->updateProducts($request, $stocktaking, [[
            'product_uuid' => $productUuid,
            'quantity_new' => $request->get('quantity_new'),
        ]]);
    }

    /**
     * @param \App\Http\Requests\StocktakingProductBatchUpdateRequest $request
     * @param \App\Models\Stocktaking                                 $stocktaking
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function batchUpdate(StocktakingProductBatchUpdateRequest $request, Stocktaking $stocktaking)
    {
        $this->authorize('update', $stocktaking);
        return $this->updateProducts($request, $stocktaking, $request->get('products'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Stocktaking  $stocktaking
     * @param array                    $baseData
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function updateProducts(Request $request, Stocktaking $stocktaking, array $baseData)
    {
        $count = 0;
        foreach ($baseData as $baseDatum) {
            $productUuid = $baseDatum['product_uuid'];
            unset($baseDatum['product_uuid']);

            $toUpdate = array_merge($baseDatum, [
                'write_off_reason_id' => $request->get('write_off_reason_id'),
                'comment' => $request->get('comment'),
            ]);

            $count += $stocktaking->products()->updateExistingPivot($productUuid, $toUpdate);
        }

        return response('', $count ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND);
    }
}
