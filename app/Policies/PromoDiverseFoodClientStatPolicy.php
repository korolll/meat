<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\PromoDiverseFoodClientStat;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromoDiverseFoodClientStatPolicy
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
     * @param \App\Models\Client                     $client
     * @param \App\Models\PromoDiverseFoodClientStat $stat
     *
     * @return bool
     */
    public function view(Client $client, PromoDiverseFoodClientStat $stat): bool
    {
        return $stat->client_uuid === $client->uuid;
    }
}
