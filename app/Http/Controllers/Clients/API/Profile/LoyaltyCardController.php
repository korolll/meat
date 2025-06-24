<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Exceptions\TealsyException;
use App\Http\Controllers\Controller;
use App\Http\Resources\LoyaltyCardResource;
use App\Http\Resources\LoyaltyCodeResource;
use App\Http\Responses\LoyaltyCardCollectionResponse;
use App\Models\Client;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyCode;
use App\Services\Framework\Http\EloquentCollectionResponse;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class LoyaltyCardController extends Controller
{
    /**
     * @throws TealsyException
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', LoyaltyCard::class);
        if (config('app.integrations.frontol.useCode')) {
            $loyaltyCard = $this->client->loyaltyCards()->first();
            if ($loyaltyCard) {
                $loyaltyCard->number = $this->getCode($this->client);

                return LoyaltyCodeResource::make($loyaltyCard);
            }
        }

        return LoyaltyCardCollectionResponse::create($this->client->loyaltyCards());
    }


    private function getCode(Client $client): string
    {
        if(!Str::isUuid($client->uuid)) {
            throw new TealsyException('Client UUID is not valid');
        }

        $loyaltyCode = LoyaltyCode::whereClientUuid($client->uuid)
            ->orderBy('expires_on', 'desc')
            ->first();

        if (null === $loyaltyCode || $loyaltyCode->expires_on->isPast()) {
            $code = Str::uuid()->toString();
            $loyaltyCode = LoyaltyCode::create([
                'client_uuid' =>  $client->uuid,
                'code' => $code,
                'expires_on' => Carbon::now()->addHours(24)
            ]);
        }

        return $loyaltyCode->code;
    }
}
