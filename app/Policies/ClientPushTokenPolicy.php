<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\ClientPushToken;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPushTokenPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\Client          $Client
     * @param \App\Models\ClientPushToken $token
     *
     * @return bool
     */
    public function destroy(Client $client, ClientPushToken $token)
    {
        return $client->uuid === $token->client_uuid;
    }
}
