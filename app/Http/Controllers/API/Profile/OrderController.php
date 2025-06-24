<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderSetStatusRequest;
use App\Http\Resources\OrderResource;
use App\Http\Responses\OrderCollectionResponse;
use App\Models\Order;
use App\Services\Management\Client\Order\StatusTransitionManagerInterface;
use Illuminate\Http\Response;

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
        return OrderCollectionResponse::create($this->user->orders());
    }

    /**
     * @param \App\Models\Order $order
     *
     * @return \App\Http\Resources\OrderResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

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
}
