<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\Profile\DeliveryAddressStoreRequest;
use App\Http\Resources\Clients\API\Profile\DeliveryAddressResource;
use App\Http\Responses\Clients\API\Profile\ClientDeliveryAddressResponse;
use App\Models\ClientDeliveryAddress;

class ClientDeliveryAddressController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', ClientDeliveryAddress::class);
        return ClientDeliveryAddressResponse::create($this->client->clientDeliveryAddresses());
    }

    /**
     * @param \App\Models\ClientDeliveryAddress $deliveryAddress
     *
     * @return \App\Http\Resources\Clients\API\Profile\DeliveryAddressResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ClientDeliveryAddress $deliveryAddress)
    {
        $this->authorize('view', $deliveryAddress);
        return DeliveryAddressResource::make($deliveryAddress);
    }

    /**
     * @param \App\Http\Requests\Clients\API\Profile\DeliveryAddressStoreRequest $request
     *
     * @return \App\Http\Resources\Clients\API\Profile\DeliveryAddressResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(DeliveryAddressStoreRequest $request)
    {
        $this->authorize('create', ClientDeliveryAddress::class);

        $deliveryAddress = new ClientDeliveryAddress($request->validated());
        $deliveryAddress->client()->associate($this->client);
        $deliveryAddress->save();

        return DeliveryAddressResource::make($deliveryAddress);
    }

    /**
     * @param \App\Http\Requests\Clients\API\Profile\DeliveryAddressStoreRequest $request
     * @param \App\Models\ClientDeliveryAddress                                  $deliveryAddress
     *
     * @return \App\Http\Resources\Clients\API\Profile\DeliveryAddressResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(DeliveryAddressStoreRequest $request, ClientDeliveryAddress $deliveryAddress)
    {
        $this->authorize('update', $deliveryAddress);

        $deliveryAddress->fill($request->validated());
        $deliveryAddress->touch();
        $deliveryAddress->save();

        return DeliveryAddressResource::make($deliveryAddress);
    }

    /**
     * @param \App\Models\ClientDeliveryAddress $deliveryAddress
     *
     * @return \App\Http\Resources\Clients\API\Profile\DeliveryAddressResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(ClientDeliveryAddress $deliveryAddress)
    {
        $this->authorize('delete', $deliveryAddress);

        $deliveryAddress->delete();
        return DeliveryAddressResource::make($deliveryAddress);
    }
}
