<?php

namespace App\Http\Controllers\Clients\API;

use App\Contracts\Database\ToQueryTransformerContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\AssortmentSearchRequest;
use App\Http\Resources\Clients\API\AssortmentResource;
use App\Http\Resources\Clients\API\AssortmentResourceCollection;
use App\Http\Responses\Clients\API\AssortmentCollectionResponse;
use App\Models\Assortment;
use App\Models\AssortmentVerifyStatus;
use App\Models\User;
use App\Services\Database\VirtualColumns\IsAssortmentClientFavorite;
use App\Services\Database\VirtualColumns\IsAssortmentClientPromoFavorite;
use App\Services\Management\Client\Product\TargetEnum;
use App\Services\Models\Assortment\Discount\AssortmentDiscountApplierInterface;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Ramsey\Uuid\Uuid;

class AssortmentController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function index()
    {
//        $this->authorize('index', Assortment::class);

        $query = Assortment::approved()
            ->select('assortments.*');

        $query->addVirtualColumn(IsAssortmentClientFavorite::class, 'is_favorite', [(string)optional($this->client)->uuid]);
        $query->addVirtualColumn(IsAssortmentClientPromoFavorite::class, 'is_promo_favorite', [(string)optional($this->client)->uuid]);
        return AssortmentCollectionResponse::create($query);
    }

    /**
     * @param string                   $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Clients\API\AssortmentResource
     */
    public function show(string $id, Request $request)
    {
        $assortmentQuery = Assortment::select('assortments.*');
        $assortmentQuery->addVirtualColumn(
            IsAssortmentClientFavorite::class,
            'is_favorite',
            [(string)optional($this->client)->uuid]
        );

        $assortmentQuery->addVirtualColumn(
            IsAssortmentClientPromoFavorite::class,
            'is_promo_favorite',
            [(string)optional($this->client)->uuid]
        );

        $storeUuid = $request->get('store_uuid');
        if (! is_scalar($storeUuid) && ! Uuid::isValid((string)$storeUuid)) {
            $storeUuid = null;
        }

        if ($storeUuid) {
            $assortmentQuery
                ->leftJoin('products', function (JoinClause $join) use ($storeUuid) {
                    $join->on('products.assortment_uuid', '=', 'assortments.uuid');
                    $join->where('products.user_uuid', '=', $storeUuid);
                })
                ->addSelect('products.quantity as products_quantity')
                ->addSelect('products.price as current_price');
        }

        /** @var Assortment $assortment */
        $assortment = $assortmentQuery->findOrFail($id);
//        $this->authorize('view', $assortment);

        // Shopping lists
        if ($this->client) {
            $assortment->user_shopping_lists = $assortment
                ->clientShoppingLists()
                ->where('client_shopping_lists.client_uuid', $this->client->uuid)
                ->get();

            $cardData = $this->client->getShoppingCart()->getData();
            $assortment->quantity_in_client_cart = (float)Arr::get($cardData, $assortment->uuid . '.quantity', 0);

            if ($storeUuid) {
                $store = User::findOrFail($storeUuid);
                $assortmentsMap = [
                    $assortment->uuid => $assortment
                ];

                /** @var AssortmentDiscountApplierInterface $applier */
                $applier = app(AssortmentDiscountApplierInterface::class);
                $applier->apply($store, $this->client, $assortmentsMap, false, true, TargetEnum::API_ASSORTMENT);
            }
        } else {
            $assortment->user_shopping_lists = [];
        }

        return AssortmentResource::make($assortment);
    }

    /**
     * @param \App\Http\Requests\Clients\API\AssortmentSearchRequest $request
     * @param \App\Contracts\Database\ToQueryTransformerContract     $toQueryTransformer
     *
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     */
    public function search(AssortmentSearchRequest $request, ToQueryTransformerContract $toQueryTransformer)
    {
//        $this->authorize('index', Assortment::class);

        $query = Assortment::search(
            $toQueryTransformer->transform($request->phrase)
        );

        // Только одобренные
        $query->where('assortment_verify_status_id', AssortmentVerifyStatus::ID_APPROVED);

        // Пагинация
        $page = (int)$request->page ?: 1;
        $size = (int)$request->per_page ?: 10;

        // todo fix it after EloquentCollectionResponse refactoring
        return AssortmentResourceCollection::collection(
            $query->paginate($size, 'page', $page)
        );
    }
}
