<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ClientExceptions\OrderStateIsFinal;
use App\Exceptions\OrderNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderAnalyticsRequest;
use App\Http\Requests\OrderSetStatusRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Http\Responses\OrderCollectionResponse;
use App\Jobs\ProcessOrderPaymentJob;
use App\Models\Order;
use App\Models\OrderPaymentType;
use App\Services\Management\Client\Order\OrderSyncUpdaterInterface;
use App\Services\Management\Client\Order\StatusTransitionManagerInterface;
use App\Services\Order\OrderAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Order::class);
        return OrderCollectionResponse::create(Order::query());
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
     * @param \App\Http\Requests\OrderUpdateRequest $request
     * @param \App\Models\Order                     $order
     *
     * @return \App\Http\Resources\OrderResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        $this->authorize('update', $order);
        if ($order->is_unchangeable_state) {
            throw new OrderStateIsFinal();
        }

        $validated = $request->validated();
        if (! $validated) {
            throw new BadRequestHttpException();
        }

        $typeId = Arr::get($validated, 'order_payment_type_id');
        if ($typeId === OrderPaymentType::ID_CASH && $order->order_payment_type_id === OrderPaymentType::ID_ONLINE) {
            throw new BadRequestHttpException('You can not change order payment-type this way');
        }

        /** @var OrderSyncUpdaterInterface $updater */
        $updater = app(OrderSyncUpdaterInterface::class);
        $savedOrder = $updater->update($order, function (Order $lockedOrder) use ($validated) {
            $lockedOrder->fill($validated);
        });

        return OrderResource::make($savedOrder);
    }

    /**
     * @param \App\Models\Order $order
     * @param bool              $deposit
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function retryPayment(Order $order, bool $deposit = true)
    {
        $this->authorize('retry-payment', $order);
        if ($order->order_payment_type_id !== OrderPaymentType::ID_ONLINE) {
            throw new BadRequestHttpException('Order has not the online payment method');
        }

        ProcessOrderPaymentJob::dispatch($order);
        return response('', Response::HTTP_NO_CONTENT);
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

    public function getOrderAnalytics(OrderAnalyticsRequest $request, OrderAnalyticsService $service): JsonResponse
    {
        try {
            $requestData = $request->validated();
            $startDate = $requestData['start_date'] . ' 00:00:00';
            $endDate = $requestData['end_date'] . ' 23:59:59';

            $orders = $service->getOrdersDoneForPeriod($startDate, $endDate);
            $averageTotalPriceWithDiscount = empty($orders->count()) ? 0.0
                : $service->getAverageTotalPriceWithDiscount($orders);

            $frequencyRepeatedPurchases = $service
                ->getFrequencyRepeatedPurchases($orders->count(), $startDate, $endDate);

            $ltv = $service->getLTV(
                $averageTotalPriceWithDiscount,
                $frequencyRepeatedPurchases,
                $startDate,
                $endDate
            );

            $averageOrderProductsCount = $service->getAverageOrderProductsCount($orders);

            $data = [
                'averageTotalPriceForPeriod' => $averageTotalPriceWithDiscount,
                'frequencyRepeatedPurchases' => $frequencyRepeatedPurchases,
                'ltv' => $ltv,
                'averageOrderItemsCount' => $averageOrderProductsCount,
            ];

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'data' => ['message' => $e->getMessage()]], 409);
        }
    }
}
