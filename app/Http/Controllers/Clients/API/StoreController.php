<?php

namespace App\Http\Controllers\Clients\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\FindNearbyStoresRequest;
use App\Http\Resources\Clients\API\StoreResource;
use App\Http\Responses\Clients\API\StoreAssortmentCollectionResponse;
use App\Http\Responses\Clients\API\StoreCollectionResponse;
use App\Models\User;
use App\Services\Database\VirtualColumns\IsAssortmentClientFavorite;
use App\Services\Database\VirtualColumns\IsAssortmentClientPromoFavorite;
use App\Services\Database\VirtualColumns\StoreDeliveryPrice;
use App\Services\Database\VirtualColumns\StoreIsFavorite;
use App\Services\Framework\Http\CollectionRequest;
use App\Services\Models\Assortment\Discount\AssortmentDiscountApplierInterface;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


class StoreController extends Controller
{
    /**
     * @param CollectionRequest $request
     *
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @noinspection PhpUnusedParameterInspection
     */
    public function index(CollectionRequest $request)
    {
        $clientUuid = $this->client?->uuid;

        $userQuery = User::store()
            ->select([
                'users.*',
                'user_delivery_zones.less_zone_distance',
                'user_delivery_zones.between_zone_distance',
                'user_delivery_zones.more_zone_distance',
                'user_delivery_zones.less_zone_price',
                'user_delivery_zones.between_zone_price',
                'user_delivery_zones.more_zone_price',
                'user_delivery_zones.max_zone_distance',
            ])
            ->leftJoin('user_delivery_zones', 'user_delivery_zones.id', '=', 'users.user_delivery_zone_id')
            ->whereNull('users.deleted_at')
            ->where('users.user_verify_status_id', '=', 'approved');

        $userQuery->addVirtualColumn(StoreIsFavorite::class, 'is_favorite', [(string)optional($this->client)->uuid]);
        $userQuery->addVirtualColumn(StoreDeliveryPrice::class, 'client_uuid', [$clientUuid]);

        return StoreCollectionResponse::create($userQuery);
    }

    /**
     * @param User $store
     *
     * @return StoreResource
     */
    public function show(User $store)
    {
//        $this->authorize('view', $store);
        $storeExtendedQuery = User::store()
            ->select(['users.*'])
            ->where('users.uuid', '=', $store->uuid);

        $storeExtendedQuery->addVirtualColumn(StoreIsFavorite::class, 'is_favorite', [(string)optional($this->client)->uuid]);
        return StoreResource::make($storeExtendedQuery->first());
    }

    /**
     * @param User $store
     *
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function showAssortments(User $store)
    {
//        $this->authorize('view', $store);
        $query = $store->assortmentMatrix()
            ->select(['assortments.*'])
            ->join('products', function (JoinClause $join) use ($store) {
                $join->on('products.assortment_uuid', '=', 'assortments.uuid');
                $join->where('products.user_uuid', '=', $store->uuid);
                $join->where('products.quantity', '>', 0);
                $join->where('products.price', '>', 0);
            })
            ->addSelect('products.quantity as products_quantity')
            ->addSelect('products.price as current_price')
            ->addVirtualColumn(IsAssortmentClientFavorite::class, 'is_favorite', [(string)optional($this->client)->uuid])
            ->addVirtualColumn(IsAssortmentClientPromoFavorite::class, 'is_promo_favorite', [(string)optional($this->client)->uuid]);

        $yellowPricesQuery = DB::table('promo_yellow_price_user')
            ->where('promo_yellow_price_user.user_uuid', $store->uuid)
            ->leftJoin('promo_yellow_prices', function (JoinClause $join) use ($store) {
                $join->on('promo_yellow_prices.uuid', '=', 'promo_yellow_price_user.promo_yellow_price_uuid');
                $join->where('promo_yellow_prices.is_enabled', true);

                $now = now();
                $join->where('start_at', '<', $now);
                $join->where('end_at', '>', $now);
                $join->whereNull('deleted_at');
            })
            ->select('promo_yellow_prices.assortment_uuid')
            ->distinct();

        $query
            ->leftJoinSub($yellowPricesQuery, 'promo_yellow', 'promo_yellow.assortment_uuid', '=', 'assortments.uuid')
            ->addSelect(DB::raw('promo_yellow.assortment_uuid IS NOT NULL as has_yellow_price'));

        if ($this->client) {
            $shoppingCartData = $this->client->getShoppingCart()->getData();
            $call = function (LengthAwarePaginator $paginator) use ($store, $shoppingCartData) {
                $assortmentsMap = [];
                /** @var \App\Models\Assortment $item */
                foreach ($paginator as $item) {
                    $assortmentsMap[$item->uuid] = $item;

                    // Also add quantity from cart
                    $item->quantity_in_client_cart = (float)Arr::get($shoppingCartData, $item->uuid . '.quantity', 0);
                }

                /** @var AssortmentDiscountApplierInterface $applier */
                $applier = app(AssortmentDiscountApplierInterface::class);
                $applier->apply($store, $this->client, $assortmentsMap, false, true);
            };

            $resp = StoreAssortmentCollectionResponse::create($query)->setBeforeToResource($call);
        } else {
            $resp = StoreAssortmentCollectionResponse::create($query);
        }

        return $resp;
    }

    /**
     * @param \App\Http\Requests\Clients\API\FindNearbyStoresRequest $request
     *
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     */
    public function findNearbyStores(FindNearbyStoresRequest $request)
    {
        $lat = (float)$request->get('latitude');
        $lng = (float)$request->get('longitude');
        $limit = (int)$request->get('limit', 5);
        $maxDistance = (int)$request->get('max_distance_meters', 50000);

        $storePoint = 'ST_Point(users.address_longitude,users.address_latitude)::geography';
        $providedPoint = "ST_Point($lng,$lat)::geography";
        $distanceStr = "ST_Distance($storePoint,$providedPoint)";

        $stores = User::store()
            ->select([
                'users.*',
                DB::raw($distanceStr . ' as distance')
            ])
            ->where('users.deleted_at', '=', NULL)
            ->where('users.user_verify_status_id', '=', 'approved')
            ->where('users.allow_find_nearby', '=', true)
            ->where(DB::raw($distanceStr), '<=', $maxDistance)
            ->orderBy(DB::raw($distanceStr))
            ->limit($limit)
            ->get();

        return StoreResource::collection($stores);
    }
}
