<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\ClientDeliveryAddress;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientDeliveryAddressPolicy
{
    use HandlesAuthorization;

    /**
     * @param Client $client
     *
     * @return bool
     */
    public function create(Client $client)
    {
        return true;
    }

    /**
     * @param Client $client
     *
     * @return bool
     */
    public function indexOwned(Client $client)
    {
        return true;
    }

    /**
     * @param Client                $client
     * @param ClientDeliveryAddress $deliveryAddress
     *
     * @return bool
     */
    public function view(Client $client, ClientDeliveryAddress $deliveryAddress)
    {
        return $deliveryAddress->client_uuid === $client->uuid;
    }

    /**
     * @param Client                $client
     * @param ClientDeliveryAddress $deliveryAddress
     *
     * @return bool
     */
    public function update(Client $client, ClientDeliveryAddress $deliveryAddress)
    {
        return $deliveryAddress->client_uuid === $client->uuid;
    }

    /**
     * @param Client                $client
     * @param ClientDeliveryAddress $deliveryAddress
     *
     * @return bool
     */
    public function delete(Client $client, ClientDeliveryAddress $deliveryAddress)
    {
        return $deliveryAddress->client_uuid === $client->uuid;
    }
}
