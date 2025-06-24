<?php


namespace App\Http\Controllers\Clients\API\Profile\Promotions;


use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\Promotions\InTheShopRequest;
use App\Http\Resources\Clients\API\Profile\PromotionInTheShopResource;
use App\Models\User;
use App\Services\Management\Profiles\Promotions\InTheShopServiceContract;
use Illuminate\Http\Response;

class InTheShopController extends Controller
{
    /**
     * Отдает данные по акции.
     * Единовременно может быть активирована только одна акция, вне зависимости от выбранного магазина
     *
     * @return \App\Http\Resources\Clients\API\Profile\PromotionInTheShopResource|array
     */
    public function index(InTheShopRequest $request)
    {
        $store = User::findOrFail($request->get('store_uuid'));

        $promotionService = app(InTheShopServiceContract::class);
        $promotion = $promotionService->getActivated($this->client, $store);

        if (! $promotion) {
            return ['data' => null];
        }

        return PromotionInTheShopResource::make($promotion);
    }

    /**
     * Создание акции
     *
     * @param InTheShopRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function store(InTheShopRequest $request)
    {
        $store = User::findOrFail($request->get('store_uuid'));

        /** @var InTheShopServiceContract $promotionService */
        $promotionService = app(InTheShopServiceContract::class);
        $promotionService->activate($this->client, $store);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
