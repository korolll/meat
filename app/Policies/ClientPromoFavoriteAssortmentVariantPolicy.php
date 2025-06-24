<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\ClientPromoFavoriteAssortmentVariant;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPromoFavoriteAssortmentVariantPolicy
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
     * @param \App\Models\Client                               $client
     * @param \App\Models\ClientPromoFavoriteAssortmentVariant $variant
     *
     * @return bool
     */
    public function activate(Client $client, ClientPromoFavoriteAssortmentVariant $variant): bool
    {
        return $variant->client_uuid === $client->uuid;
    }
}
