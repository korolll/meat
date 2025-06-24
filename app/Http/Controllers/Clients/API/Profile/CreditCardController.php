<?php


namespace App\Http\Controllers\Clients\API\Profile;


use App\Exceptions\ClientExceptions\AcquireActionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\LinkCardRequest;
use App\Http\Requests\Clients\API\LinkCardResultRequest;
use App\Http\Resources\Clients\API\Profile\CreditCardResource;
use App\Models\ClientCreditCard;
use App\Models\ClientPayment;
use App\Models\OrderPaymentType;
use App\Models\OrderStatus;
use App\Models\PaymentVendor;
use App\Models\PaymentVendorSetting;
use App\Models\User;
use App\Services\Management\Client\Order\Payment\PaymentStatusEnum;
use App\Services\Money\Acquire\Resolver\AcquireResolverInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Voronkovich\SberbankAcquiring\Exception\ActionException;

class CreditCardController extends Controller
{
    /**
     * @var AcquireResolverInterface
     */
    private AcquireResolverInterface $acquireResolver;

    /**
     * @param AcquireResolverInterface $acquireResolver
     */
    public function __construct(AcquireResolverInterface $acquireResolver)
    {
        $this->acquireResolver = $acquireResolver;
    }

    /**
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', ClientCreditCard::class);
        $cards = $this->client
            ->clientCreditCards()
            ->whereNotNull('binding_id')
            ->get();

        return CreditCardResource::collection($cards);
    }

    /**
     * @param \App\Models\ClientCreditCard $creditCard
     *
     * @return \App\Http\Resources\Clients\API\Profile\CreditCardResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ClientCreditCard $creditCard)
    {
        $this->authorize('view', $creditCard);
        return CreditCardResource::make($creditCard);
    }

    /**
     * @param \App\Models\ClientCreditCard $creditCard
     *
     * @return \App\Http\Resources\Clients\API\Profile\CreditCardResource
     * @throws \App\Exceptions\ClientExceptions\AcquireActionException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Voronkovich\SberbankAcquiring\Exception\SberbankAcquiringException
     */
    public function destroy(ClientCreditCard $creditCard)
    {
        $this->authorize('delete', $creditCard);
        if ($creditCard->binding_id) {
            // Check not paid orders
            $exist = $creditCard->orders()
                ->whereIn('order_status_id', [
                    OrderStatus::ID_NEW,
                    OrderStatus::ID_COLLECTING,
                ])
                ->where('order_payment_type_id', OrderPaymentType::ID_ONLINE)
                ->exists();

            if ($exist) {
                throw new BadRequestHttpException('Your card is used to pay in active orders');
            }

//            try {
//                $this->acquireHandler->unbindCard($creditCard->binding_id);
//            } catch (ActionException $exception) {
//                throw AcquireActionException::fromActionException($exception);
//            }
        }

        $creditCard->delete();
        return CreditCardResource::make($creditCard);
    }

    /**
     * @return array[]
     * @throws \App\Exceptions\ClientExceptions\AcquireActionException
     * @throws \Throwable
     * @throws \Voronkovich\SberbankAcquiring\Exception\SberbankAcquiringException
     */
    public function linkCard(LinkCardRequest $request)
    {
        $client = $this->client;
        $generatedOrderUuid = Uuid::uuid4()->toString();
        $storeUuid = $request->input('store_uuid');
        if (!$storeUuid) {
            $storeUuid = $this->client->selected_store_user_uuid;
        }

        $acquire = null;
        $vendorId = null;
        $paymentSetting = null;

        if ($storeUuid) {
            /** @var User $store */
            $store = User::findOrFail($storeUuid);
            /** @var PaymentVendorSetting $paymentSetting */
            $paymentSetting = $store->paymentVendorSettingsIsActive()->first();
            if ($paymentSetting) {
                $acquire = $this->acquireResolver->resolveBySetting($paymentSetting);
                $vendorId = $acquire->getVendorId();
            }
        }

        if (!$acquire) {
            $acquire = $this->acquireResolver->resolveDefaultByVendor();
            $vendorId = $acquire->getVendorId();
        }

        // Create new card
        $card = new ClientCreditCard();
        $card->client()->associate($client);

        $amount = $this->getBindCardAmount($vendorId);
        try {
            $result = $acquire->registerPaymentForBinding(
                $client->uuid,
                $generatedOrderUuid,
                $amount,
                route('web.success-payment'),
                route('web.error-payment')
            );
        } catch (ActionException $exception) {
            throw AcquireActionException::fromActionException($exception);
        }

        DB::transaction(function () use ($card, $client, $result, $amount, $generatedOrderUuid, $paymentSetting) {
            $card->virtual_order_uuid = $generatedOrderUuid;
            $card->generated_order_uuid = $result->id;
            $card->payment_vendor_setting_uuid = $paymentSetting?->uuid;
            $card->save();

            $payment = new ClientPayment();
            $payment->client()->associate($client);
            $payment->relatedReference()->associate($card);
            $payment->generated_order_uuid = $result->id;
            $payment->amount = $amount;
            $payment->save();
        });

        return [
            'data' => [
                'form_url' => $result->confirmationUrl,
                'order_id' => $result->id,
            ]
        ];
    }

    /**
     * @param \App\Http\Requests\Clients\API\LinkCardResultRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \App\Exceptions\ClientExceptions\AcquireActionException
     * @throws \Throwable
     * @throws \Voronkovich\SberbankAcquiring\Exception\SberbankAcquiringException
     */
    public function linkCardSuccess(LinkCardResultRequest $request)
    {
        $orderUuid = $request->get('orderId');
        $card = $this->findCard($orderUuid);
        $acquire = $this->acquireResolver->resolveByClientCard($card);

        try {
            $result = $acquire->getPaymentStatus($orderUuid);
        } catch (ActionException $exception) {
            throw AcquireActionException::fromActionException($exception);
        }

        $orderStatus = $result->status;
        if ($orderStatus !== PaymentStatusEnum::APPROVED && $orderStatus !== PaymentStatusEnum::DEPOSITED) {
            throw new BadRequestHttpException('Bad state of order');
        }

        /** @var ClientPayment $payment */
        $payment = $card->relatedClientPayments()
            ->where('client_payments.generated_order_uuid', $orderUuid)
            ->first();

        $payment->order_status = $orderStatus;
        $card->binding_id = $result->bindingId;
        if (! $card->binding_id) {
            throw new BadRequestHttpException('Bad response from acquire provider');
        }

        try {
            $acquire->refund($orderUuid, $this->getBindCardAmount($acquire->getVendorId()));
        } catch (ActionException $exception) {
            throw AcquireActionException::fromActionException($exception);
        }

        $payment->order_status = PaymentStatusEnum::REFUNDED;
        $card->card_mask = $result->cardNumberMasked;
        DB::transaction(function () use ($card, $payment) {
            $card->save();
            $payment->save();
        });

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Http\Requests\Clients\API\LinkCardResultRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \App\Exceptions\ClientExceptions\AcquireActionException
     * @throws \Throwable
     * @throws \Voronkovich\SberbankAcquiring\Exception\SberbankAcquiringException
     */
    public function linkCardError(LinkCardResultRequest $request)
    {
        $orderUuid = $request->get('orderId');
        $card = $this->findCard($orderUuid);
        $acquire = $this->acquireResolver->resolveByClientCard($card);

        try {
            $result = $acquire->getPaymentStatus($orderUuid);
        } catch (ActionException $exception) {
            throw AcquireActionException::fromActionException($exception);
        }

        /** @var ClientPayment $payment */
        $payment = $card->relatedClientPayments()
            ->where('client_payments.generated_order_uuid', $orderUuid)
            ->first();

        $payment->order_status = $result->status;
        $payment->external_status = $result->originalStatus;
        DB::transaction(function () use ($payment, $card) {
            $payment->save();
            $card->delete();
        });
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string $orderUuid
     *
     * @return \App\Models\ClientCreditCard
     */
    protected function findCard(string $orderUuid): ClientCreditCard
    {
        /** @var ClientCreditCard $card */
        $card = $this->client->clientCreditCards()
            ->where('generated_order_uuid', $orderUuid)
            ->first();

        if (! $card) {
            throw new BadRequestHttpException('Card not found');
        }

        return $card;
    }

    /**
     * @param string $vendorId
     * @return int
     */
    protected function getBindCardAmount(string $vendorId): int
    {
        if ($vendorId == PaymentVendor::ID_YOOKASSA) {
            return ((int)config('services.yookassa.acquire.bind_card_amount')) ?: 100;
        }

        return ((int)config('services.sberbank.acquire.bind_card_amount')) ?: 100;
    }
}
