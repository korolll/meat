<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\OrderCalculateRequest;
use App\Http\Requests\Clients\API\Profile\OrderProductSetRatingRequest;
use App\Http\Requests\OrderSetStatusRequest;
use App\Http\Requests\Clients\API\Profile\OrderStoreRequest;
use App\Http\Resources\Clients\API\Profile\OrderResource;
use App\Http\Resources\OrderResourceWithRating;
use App\Http\Responses\Clients\API\Profile\OrderCollectionResponse;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Services\Debug\DebugDataCollector;
use App\Services\Management\Client\Order\OrderFactoryInterface;
use App\Services\Management\Client\Order\StatusTransitionManagerInterface;
use App\Services\Management\Client\Product\Discount\Concrete\PromocodeDiscountResolver;
use App\Services\Management\Rating\OrderProductRatingScoreFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class OrderController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', Order::class);

        return OrderCollectionResponse::create(
            $this->client->orders()
        );
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return OrderResourceWithRating
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return OrderResourceWithRating::make($order);
    }

    /**
     * @param \App\Http\Requests\Clients\API\Profile\OrderCalculateRequest $request
     *
     * @return \App\Http\Resources\Clients\API\Profile\OrderResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function calculate(OrderCalculateRequest $request)
    {
        $this->authorize('calculate', Order::class);
        $validated = $request->validated();
        $this->validateProductUuids($validated);

        $bonusesToPay = Arr::get($validated, 'order.paid_bonus');
        if ($bonusesToPay > 0) {
            $client = $this->client;
            if ($client->bonus_balance < $bonusesToPay) {
                throw new BadRequestHttpException('Not enough bonuses');
            }
        }

        /** @var DebugDataCollector $debugCollection */
        $debugCollection = app(DebugDataCollector::class);
        $order = $debugCollection->measure('OrderController:calculate:make', function () use ($validated) {
            /** @var OrderFactoryInterface $factory */
            $factory = app(OrderFactoryInterface::class);
            return $factory->make(
                $this->client,
                $validated['order'],
                $validated['products'],
            );
        });

        $order->promocode = PromocodeDiscountResolver::getPromocode();

        return OrderResource::make($order);
    }

    /**
     * @param \App\Http\Requests\Clients\API\Profile\OrderStoreRequest $request
     *
     * @return \App\Http\Resources\Clients\API\Profile\OrderResource
     * @throws \Illuminate\Auth\Access\AuthorizationException|\Illuminate\Validation\ValidationException
     */
    public function store(OrderStoreRequest $request)
    {
        $this->authorize('create', Order::class);
        $validated = $request->validated();
        $this->validateProductUuids($validated);

        /** @var DebugDataCollector $debugCollection */
        $debugCollection = app(DebugDataCollector::class);
        $order = $debugCollection->measure('OrderController:store:create', function () use ($validated) {
            /** @var OrderFactoryInterface $factory */
            $factory = app(OrderFactoryInterface::class);
            return $factory->create(
                $this->client,
                $validated['order'],
                $validated['products'],
            );
        });

        return OrderResource::make($order);
    }

    /**
     * @param \App\Http\Requests\OrderSetStatusRequest $request
     * @param \App\Models\Order                        $order
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function setStatus(OrderSetStatusRequest $request, Order $order)
    {
        $this->authorize('set-status', $order);

        $manager = app(StatusTransitionManagerInterface::class);
        $manager->transition($order, $this->user, $request->get('order_status_id'));

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Http\Requests\Clients\API\Profile\OrderProductSetRatingRequest $request
     * @param \App\Models\OrderProduct                                            $product
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setProductRating(OrderProductSetRatingRequest $request, OrderProduct $product)
    {
        $this->authorize('set-rating', $product);
        if ($product->order->order_status_id !== OrderStatus::ID_DONE) {
            throw new BadRequestHttpException('Order is not done');
        }

        /** @var OrderProductRatingScoreFactory $ratingScoreFactory */
        $ratingScoreFactory = app('factory.rating-score.order-product');
        $ratingScoreFactory->create($product->product->assortment, $this->client, $product, $request->get('value'), [
            'comment' => $request->get('comment'),
        ]);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param array $validated
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateProductUuids(array $validated): void
    {
        $products = $validated['products'];
        $storeUuid = $validated['order']['store_user_uuid'];

        $uuids = [];
        $uuidsList = [];
        foreach ($products as $key => $data) {
            $uuid = $data['assortment_uuid'];
            $uuidsList[] = $uuid;
            $uuids[$uuid] = $key;
        }

        $result = Product::query()
            ->whereIn('assortment_uuid', $uuidsList)
            ->where('user_uuid', $storeUuid)
            ->where('price', '>', 0)
            ->select(['uuid', 'assortment_uuid'])
            ->get();

        foreach ($result as $product) {
            unset($uuids[$product->assortment_uuid]);
        }

        if ($uuids) {
            $validator = ValidatorFacade::make($validated, []);
            foreach ($uuids as $uuid => $key) {
                $validator->addFailure("products.$key.assortment_uuids", 'exists', ['products', 'assortment_uuid']);
            }

            throw new ValidationException($validator);
        }
    }
}
