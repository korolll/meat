<?php

namespace App\Http\Controllers\Integrations\CashRegisters\API;

use App\Exceptions\ClientExceptions\AuthenticationCodeInvalidException;
use App\Exceptions\ClientExceptions\ClientAlreadyAssociatedWithLoyaltyCardOfSameTypeException;
use App\Exceptions\ClientExceptions\LoyaltyCardAlreadyAssociatedWithClientException;
use App\Exceptions\ClientExceptions\LoyaltyCardNotAssociatedWithClientException;
use App\Exceptions\ClientExceptions\LoyaltyCardNotFoundException;
use App\Exceptions\ClientExceptions\LoyaltyCardTypeNotAssociatedWithStoreException;
use App\Exceptions\ClientExceptions\LoyaltyCardTypeNotFoundException;
use App\Exceptions\ClientExceptions\ClientNotFoundException;
use App\Exceptions\ClientExceptions\StoreNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\CashRegisters\API\AssociateLoyaltyCardRequest;
use App\Http\Requests\Integrations\CashRegisters\API\FindLoyaltyCardRequest;
use App\Http\Requests\Integrations\CashRegisters\API\FindLoyaltyCardByPhoneRequest;
use App\Models\Client;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyCardType;
use App\Models\User;
use App\Notifications\Clients\API\AuthenticationCode;
use App\Services\Authentication\ClientAuthenticationManagerContract;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LoyaltyCardController extends Controller
{
    /**
     * @var ClientAuthenticationManagerContract
     */
    protected $authenticationManager;

    /**
     * @param ClientAuthenticationManagerContract $authenticationManager
     */
    public function __construct(ClientAuthenticationManagerContract $authenticationManager)
    {
        $this->middleware('static-token:' . config('app.integrations.cash-registers.token'));

        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @param FindLoyaltyCardRequest $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    public function find(FindLoyaltyCardRequest $request)
    {
        if (($store = $this->findStore($request->user_uuid)) === null) {
            throw new StoreNotFoundException();
        }

        if (($loyaltyCardType = $this->findLoyaltyCardType($request->loyalty_card_type_uuid)) === null) {
            throw new LoyaltyCardTypeNotFoundException();
        }

        if ($this->isLoyaltyCardAssociatedWithStore($loyaltyCardType, $store) === false) {
            throw new LoyaltyCardTypeNotAssociatedWithStoreException();
        }

        if (($loyaltyCard = $this->findLoyaltyCard($loyaltyCardType, $request->loyalty_card_number)) === null) {
            throw new LoyaltyCardNotFoundException();
        }

        if ($this->isLoyaltyCardAssociatedWithSomeClient($loyaltyCard) === false) {
            throw new LoyaltyCardNotAssociatedWithClientException();
        }

        return [
            'data' => [
                'uuid' => $loyaltyCard->uuid,
                'discount_percent' => $loyaltyCard->discount_percent,
                'loyalty_card_number' => $loyaltyCard->number,
            ],
        ];
    }

    /**
     * @param AssociateLoyaltyCardRequest $request
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Throwable
     */
    public function associate(AssociateLoyaltyCardRequest $request)
    {
        $client = Client::firstOrCreate(['phone' => $request->phone]);

        if (($loyaltyCardType = $this->findLoyaltyCardType($request->loyalty_card_type_uuid)) === null) {
            throw new LoyaltyCardTypeNotFoundException();
        }

        if ($client->loyaltyCards()->where('loyalty_card_type_uuid', $loyaltyCardType->uuid)->exists()) {
            throw new ClientAlreadyAssociatedWithLoyaltyCardOfSameTypeException();
        }

        if (($loyaltyCard = $this->findLoyaltyCard($loyaltyCardType, $request->loyalty_card_number)) === null) {
            throw new LoyaltyCardNotFoundException();
        }

        if ($loyaltyCard->client_uuid !== null) {
            throw new LoyaltyCardAlreadyAssociatedWithClientException();
        }

        if ($request->code === null) {
            return $this->generateAuthenticationCodeAndNotifyClient($client);
        }

        if ($this->authenticationManager->validateAuthenticationCode($client, $request->code) === false) {
            throw new AuthenticationCodeInvalidException();
        }

        DB::transaction(function () use ($request, $client, $loyaltyCard) {
            if ($request->client_name) {
                $client->name = $request->client_name;
                $client->saveOrFail();
            }

            $loyaltyCard->client()->associate($client);
            $loyaltyCard->saveOrFail();
        });

        return [
            'data' => [
                'uuid' => $loyaltyCard->uuid,
                'discount_percent' => $loyaltyCard->discount_percent,
                'loyalty_card_number' => $loyaltyCard->number,
            ],
        ];
    }

    /**
     * @param FindLoyaltyCardByPhoneRequest $request
     * @return mixed
     */
    public function findloyaltycardbyphone(FindLoyaltyCardByPhoneRequest $request)
    {
        $client = Client::Where(['phone' => $request->phone])->first();

        if ($client == null) {
        throw new ClientNotFoundException();
        }

        if ($client->loyaltyCards()->first() == null) {
        throw new ClientNotFoundException();
        }

        return [
            'data' => [
                'uuid' => $client->loyaltyCards()->firstOrFail()->uuid,
                'discount_percent' => $client->loyaltyCards()->firstOrFail()->discount_percent,
                'loyalty_card_number' => $client->loyaltyCards()->firstOrFail()->number,
            ],
        ];
    }

    /**
     * @param string $uuid
     * @return User|null
     */
    protected function findStore(string $uuid): ?User
    {
        return User::store()->find($uuid);
    }

    /**
     * @param string $loyaltyCardTypeUuid
     * @return LoyaltyCardType|null
     */
    protected function findLoyaltyCardType(string $loyaltyCardTypeUuid): ?LoyaltyCardType
    {
        return LoyaltyCardType::find($loyaltyCardTypeUuid);
    }

    /**
     * @param LoyaltyCardType $loyaltyCardType
     * @param User $store
     * @return bool
     */
    protected function isLoyaltyCardAssociatedWithStore(LoyaltyCardType $loyaltyCardType, User $store): bool
    {
        return $store->loyaltyCardTypes()->whereKey($loyaltyCardType->uuid)->exists();
    }

    /**
     * @param LoyaltyCardType $loyaltyCardType
     * @param string $loyaltyCardNumber
     * @return LoyaltyCard|null
     */
    protected function findLoyaltyCard(LoyaltyCardType $loyaltyCardType, string $loyaltyCardNumber): ?LoyaltyCard
    {
        return LoyaltyCard::hasTypeNumber($loyaltyCardType->uuid, $loyaltyCardNumber)->first();
    }

    /**
     * @param LoyaltyCard $loyaltyCard
     * @return bool
     */
    protected function isLoyaltyCardAssociatedWithSomeClient(LoyaltyCard $loyaltyCard): bool
    {
        return $loyaltyCard->client_uuid !== null;
    }

    /**
     * @param Client $client
     * @return mixed
     */
    protected function generateAuthenticationCodeAndNotifyClient(Client $client)
    {
        $code = $this->authenticationManager->generateAuthenticationCode($client);

        $client->notify(AuthenticationCode::make($code));

        return response(['message' => 'Authentication code was successfully sent'], Response::HTTP_CREATED);
    }
}