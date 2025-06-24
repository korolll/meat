<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MealReceiptTabStoreRequest;
use App\Http\Resources\MealReceiptTabResource;
use App\Http\Responses\MealReceiptTabCollectionResponse;
use App\Models\MealReceiptTab;
use Illuminate\Http\Response;

class MealReceiptTabController extends Controller
{
    public function index()
    {
        $this->authorize('index', MealReceiptTab::class);
        return MealReceiptTabCollectionResponse::create(MealReceiptTab::query());
    }

    public function store(MealReceiptTabStoreRequest $request)
    {
        $this->authorize('create', MealReceiptTab::class);

        $validated = $request->validated();
        $mealReceiptTab = new MealReceiptTab($validated);
        $mealReceiptTab->save();

        return MealReceiptTabResource::make($mealReceiptTab);
    }

    public function show(MealReceiptTab $mealReceiptTab)
    {
        $this->authorize('view', $mealReceiptTab);
        return MealReceiptTabResource::make($mealReceiptTab);
    }

    public function update(MealReceiptTabStoreRequest $request, MealReceiptTab $mealReceiptTab)
    {
        $this->authorize('update', $mealReceiptTab);

        $validated = $request->validated();
        $mealReceiptTab->fill($validated);
        $mealReceiptTab->save();

        return MealReceiptTabResource::make($mealReceiptTab);
    }

    public function destroy(MealReceiptTab $mealReceiptTab)
    {
        $this->authorize('delete', $mealReceiptTab);
        $mealReceiptTab->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
