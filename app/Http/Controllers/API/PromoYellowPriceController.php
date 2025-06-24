<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoYellowPriceBulkStoreRequest;
use App\Http\Requests\PromoYellowPriceStoreRequest;
use App\Http\Resources\PromoYellowPriceResource;
use App\Http\Responses\PromoYellowPriceCollectionResponse;
use App\Models\PromoYellowPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoYellowPriceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('index', PromoYellowPrice::class);

        $query = PromoYellowPrice::query()->with('stores');

        if ($request->filled('store_uuid')) {
            $storeUuids = $request->input('store_uuid');
            $query->whereHas('stores', function ($q) use ($storeUuids) {
                $q->whereIn('uuid', $storeUuids);
            });
        }

        return PromoYellowPriceCollectionResponse::create($query);
    }

    public function store(PromoYellowPriceStoreRequest $request)
    {
        $this->authorize('create', PromoYellowPrice::class);

        $data = $request->validated();
        $promo = new PromoYellowPrice($data);
        DB::transaction(function () use($promo, $data) {
            $promo->save();
            $promo->stores()->sync($data['store_uuids']);
        });
        return PromoYellowPriceResource::make($promo);
    }

    public function bulkStore(PromoYellowPriceBulkStoreRequest $request)
    {
        $this->authorize('create', PromoYellowPrice::class);

        $data = $request->validated();
        $createdPromos = [];

        DB::transaction(function () use ($data, &$createdPromos) {
            foreach ($data['assortment_uuids'] as $assortmentUuid) {
                $promo = new PromoYellowPrice([
                    'assortment_uuid' => $assortmentUuid,
                    'price' => $data['price'],
                    'is_enabled' => $data['is_enabled'],
                    'start_at' => $data['start_at'],
                    'end_at' => $data['end_at'],
                ]);

                $promo->save();
                $promo->stores()->sync($data['store_uuids']);

                $createdPromos[] = $promo;
            }
        });

        return PromoYellowPriceResource::collection($createdPromos);
    }

    public function update(PromoYellowPrice $promoYellowPrice, PromoYellowPriceStoreRequest $request)
    {
        $this->authorize('update', $promoYellowPrice);
        $data = $request->validated();
        $promoYellowPrice->fill($data);

        DB::transaction(function () use($promoYellowPrice, $data) {
            $promoYellowPrice->save();
            $promoYellowPrice->stores()->sync($data['store_uuids']);
        });
        return PromoYellowPriceResource::make($promoYellowPrice);
    }

    public function show(PromoYellowPrice $promoYellowPrice)
    {
        $this->authorize('show', $promoYellowPrice);
        return PromoYellowPriceResource::make($promoYellowPrice);
    }

    public function destroy(PromoYellowPrice $promoYellowPrice)
    {
        $this->authorize('destroy', $promoYellowPrice);

        $promoYellowPrice->delete();
        return PromoYellowPriceResource::make($promoYellowPrice);
    }

    public function toggleEnable(PromoYellowPrice $promoYellowPrice)
    {
        $this->authorize('toggle', $promoYellowPrice);
        $promoYellowPrice->is_enabled = !$promoYellowPrice->is_enabled;
        $promoYellowPrice->save();
        return PromoYellowPriceResource::make($promoYellowPrice);
    }
}
