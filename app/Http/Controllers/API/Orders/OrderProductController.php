<?php

namespace App\Http\Controllers\API\Orders;

use App\Exceptions\ClientExceptions\OrderStateIsFinal;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Orders\StoreOrderProductRequest;
use App\Http\Requests\API\Orders\UpdateOrderProductRequest;
use App\Http\Requests\Clients\API\Profile\OrderProductSetRatingRequest;
use App\Http\Requests\Clients\API\Profile\ReceiptLineSetRatingRequest;
use App\Http\Resources\OrderProductResource;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Services\Management\Client\Order\OrderProductChangerInterface;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderProductController extends Controller
{
    /**
     * @param \App\Models\OrderProduct $product
     *
     * @return \App\Http\Resources\OrderProductResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(OrderProduct $product)
    {
        $this->authorize('view', $product);

        return OrderProductResource::make($product);
    }

    /**
     * @param \App\Http\Requests\API\Orders\StoreOrderProductRequest $request
     *
     * @return \App\Http\Resources\OrderProductResource
     * @throws \App\Exceptions\ClientExceptions\OrderStateIsFinal
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreOrderProductRequest $request)
    {
        $validated = $request->validated();
        $order = Order::findOrFail($validated['order_uuid']);

        $this->authorize('create', [OrderProduct::class, $order]);
        if ($order->is_unchangeable_state) {
            throw new OrderStateIsFinal();
        }

        $validated = $request->validated();
        $store = $order->store;
        /** @var \App\Models\Product $product */
        $product = $store->products()
            ->where('assortment_uuid', $validated['assortment_uuid'])
            ->where('price', '>', 0)
            ->first();

        if (! $product) {
            throw new BadRequestHttpException('Product not found');
        }

        $alreadyExist = $order->orderProducts()
            ->where('product_uuid', $product->uuid)
            ->exists();

        if ($alreadyExist) {
            throw new BadRequestHttpException('Product is already exist in the order');
        }

        $validated['product_uuid'] = $product->uuid;
        unset($validated['assortment_uuid']);

        /** @var OrderProductChangerInterface $productChanger */
        $productChanger = app(OrderProductChangerInterface::class);
        $orderProduct = $productChanger->addProduct($validated);

        return OrderProductResource::make($orderProduct);
    }

    /**
     * @param \App\Http\Requests\API\Orders\UpdateOrderProductRequest $request
     * @param \App\Models\OrderProduct                                $product
     *
     * @return \App\Http\Resources\OrderProductResource
     * @throws \App\Exceptions\ClientExceptions\OrderStateIsFinal
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateOrderProductRequest $request, OrderProduct $product)
    {
        $this->authorize('update', $product);
        if ($product->order->is_unchangeable_state) {
            throw new OrderStateIsFinal();
        }

        /** @var OrderProductChangerInterface $productChanger */
        $productChanger = app(OrderProductChangerInterface::class);
        $product = $productChanger->updateProductQuantity(current($request->validated()), $product);

        return OrderProductResource::make($product);
    }

    /**
     * @param \App\Http\Requests\Clients\API\Profile\OrderProductSetRatingRequest $request
     * @param \App\Models\OrderProduct                                            $product
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function setRating(OrderProductSetRatingRequest $request, OrderProduct $product)
    {
        $this->authorize('set-rating', $product);

        $ratingScoreFactory = app('factory.rating-score.order-product');
        $ratingScoreFactory->create($product->product->assortment, $this->client, $product, $request->get('value'), [
            'comment' => $request->get('comment'),
        ]);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
