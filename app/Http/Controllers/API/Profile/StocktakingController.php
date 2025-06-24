<?php

namespace App\Http\Controllers\API\Profile;

use App\Exceptions\ClientExceptions\StocktakingHasNoProductsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StocktakingStoreRequest;
use App\Http\Resources\StocktakingResource;
use App\Http\Responses\StocktakingCollectionResponse;
use App\Models\Stocktaking;
use App\Services\Management\Stocktaking\Contracts\ProductSynchronizerContract;
use Illuminate\Support\Facades\DB;

class StocktakingController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', Stocktaking::class);

        return StocktakingCollectionResponse::create(
            $this->user->stocktakings()
        );
    }

    /**
     * @param StocktakingStoreRequest $request
     * @param ProductSynchronizerContract $productSynchronizer
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(StocktakingStoreRequest $request, ProductSynchronizerContract $productSynchronizer)
    {
        $this->authorize('create', Stocktaking::class);

        // @todo В дальнейшем нужно зарефакторить, это должно переехать в отдельный сервис
        return DB::transaction(function () use ($request, $productSynchronizer) {
            $stocktaking = new Stocktaking();
            $stocktaking->user()->associate($this->user);
            $stocktaking->save();

            if ($productSynchronizer->synchronize($stocktaking, $request->getCatalogUuids()) === 0) {
                throw new StocktakingHasNoProductsException();
            }

            return StocktakingResource::make($stocktaking);
        });
    }

    /**
     * @param Stocktaking $stocktaking
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function approve(Stocktaking $stocktaking)
    {
        $this->authorize('update', $stocktaking);

        $stocktaking->approved_at = now();
        $stocktaking->saveOrFail();

        return StocktakingResource::make($stocktaking);
    }
}
