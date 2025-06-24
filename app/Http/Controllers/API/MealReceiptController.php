<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Clients\CreateStoryReactionRequest;
use App\Http\Requests\MealReceiptStoreRequest;
use App\Http\Resources\MealReceiptResource;
use App\Http\Responses\MealReceiptCollectionResponse;
use App\Models\MealReceipt;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MealReceiptController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', MealReceipt::class);
        return MealReceiptCollectionResponse::create(MealReceipt::query());
    }

    /**
     * @param \App\Http\Requests\MealReceiptStoreRequest $request
     *
     * @return \App\Http\Resources\MealReceiptResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(MealReceiptStoreRequest $request)
    {
        $this->authorize('create', MealReceipt::class);

        $validated = $request->validated();
        $mealReceipt = new MealReceipt($validated);
        $mealReceipt->save();

        $mealReceipt->assortments()->sync($request->get('assortment_uuids', []));

        return MealReceiptResource::make($mealReceipt);
    }

    /**
     * @param \App\Models\MealReceipt $mealReceipt
     *
     * @return \App\Http\Resources\MealReceiptResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(MealReceipt $mealReceipt)
    {
        $this->authorize('view', $mealReceipt);
        return MealReceiptResource::make($mealReceipt);
    }

    /**
     * @param \App\Http\Requests\MealReceiptStoreRequest $request
     * @param \App\Models\MealReceipt                    $mealReceipt
     *
     * @return \App\Http\Resources\MealReceiptResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(MealReceiptStoreRequest $request, MealReceipt $mealReceipt)
    {
        $this->authorize('update', $mealReceipt);

        $validated = $request->validated();
        $mealReceipt->fill($validated);
        $mealReceipt->save();

        $mealReceipt->assortments()->sync($request->get('assortment_uuids', []));

        return MealReceiptResource::make($mealReceipt);
    }

    /**
     * @param \App\Models\MealReceipt $mealReceipt
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(MealReceipt $mealReceipt)
    {
        $this->authorize('delete', $mealReceipt);
        $mealReceipt->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
