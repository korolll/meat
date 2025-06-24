<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\PromoDiverseFoodClientDiscount;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromoDiverseFoodClientDiscountPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\Client $client
     *
     * @return bool
     */
    public function indexOwned(Client $client): bool
    {
        return true;
    }

    /**
     * @param \App\Models\Client                         $client
     * @param \App\Models\PromoDiverseFoodClientDiscount $discount
     *
     * @return bool
     */
    public function view(Client $client, PromoDiverseFoodClientDiscount $discount): bool
    {
        return $discount->client_uuid === $client->uuid;
    }
}
