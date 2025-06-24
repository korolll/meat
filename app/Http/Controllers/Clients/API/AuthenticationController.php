<?php

namespace App\Http\Controllers\Clients\API;

use App\Exceptions\ClientExceptions\AuthenticationCodeInvalidException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\LoginViaLoyaltyCardRequest;
use App\Http\Requests\Clients\API\LoginViaPhoneRequest;
use App\Http\Requests\Clients\API\ResetViaLoyaltyCardRequest;
use App\Models\Client;
use App\Models\LoyaltyCard;
use App\Notifications\Clients\API\AuthenticationCode;
use App\Services\Authentication\ClientAuthenticationManagerContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthenticationController extends Controller
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
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @param LoginViaPhoneRequest $request
     *
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    public function loginViaPhone(LoginViaPhoneRequest $request)
    {
        $client = Client::firstOrCreate(['phone' => $request->phone]);

        return $this->login($client, $request->code, true);
    }

    /**
     * @param LoginViaLoyaltyCardRequest $request
     *
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    public function loginViaLoyaltyCard(LoginViaLoyaltyCardRequest $request)
    {
        $loyaltyCard = LoyaltyCard::hasTypeNumber($request->loyalty_card_type_uuid, $request->loyalty_card_number)
            ->firstOrFail();

        return $this->login($loyaltyCard->client, $request->code);
    }

    /**
     * @param ResetViaLoyaltyCardRequest $request
     *
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Throwable
     */
    public function resetViaLoyaltyCard(ResetViaLoyaltyCardRequest $request)
    {
        $loyaltyCard = LoyaltyCard::hasTypeNumber($request->loyalty_card_type_uuid, $request->loyalty_card_number)
            ->firstOrFail();

        if (substr($loyaltyCard->client->phone, -4) !== $request->old_phone_last_four_digits) {
            throw new ModelNotFoundException();
        }

        $loyaltyCard->client->phone = $request->new_phone;
        $loyaltyCard->client->saveOrFail();

        return $this->login($loyaltyCard->client, $request->code);
    }

    /**
     * @param \App\Models\Client $client
     * @param                    $code
     * @param bool               $generateCardIfNotExist
     *
     * @return array|mixed
     * @throws \App\Exceptions\ClientExceptions\AuthenticationCodeInvalidException
     */
    protected function login(Client $client, $code, bool $generateCardIfNotExist = false)
    {
        if ($code === null) {
            return $this->generateAuthenticationCodeAndNotifyClient($client);
        }

        if ($this->authenticationManager->validateAuthenticationCode($client, $code) === false) {
            throw new AuthenticationCodeInvalidException();
        }

        if ($generateCardIfNotExist && ! $client->loyaltyCards()->exists()) {
            $cardTypes = (array)config('app.clients.loyalty_card_types_for_generating');
            if (! $cardTypes) {
                throw new BadRequestHttpException('Missing loyalty card types in config');
            }

            /** @var LoyaltyCard $freeCard */
            $freeCard = LoyaltyCard::whereIn('loyalty_card_type_uuid', $cardTypes)
                ->whereNull('client_uuid')
                ->first();

            if (! $freeCard) {
                throw new BadRequestHttpException('Missing free loyalty card');
            }

            $freeCard
                ->client()
                ->associate($client)
                ->save();
        }

        $token = $this->authenticationManager->generateAuthenticationToken($client);
        return compact('token');
    }

    /**
     * @param Client $client
     *
     * @return mixed
     */
    protected function generateAuthenticationCodeAndNotifyClient(Client $client)
    {
        $code = $this->authenticationManager->generateAuthenticationCode($client);

        if (!$client->isDefaultClient()) {
            $client->notify(AuthenticationCode::make($code));
        }

        return response(['message' => 'Authentication code was successfully sent'], Response::HTTP_CREATED);
    }
}
