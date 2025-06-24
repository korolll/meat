<?php

namespace App\Policies;

use App\Models\Client;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientActivePromoFavoriteAssortmentPolicy
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
}
