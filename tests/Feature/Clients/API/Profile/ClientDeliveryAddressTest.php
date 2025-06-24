<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Client;
use App\Models\ClientDeliveryAddress;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class ClientDeliveryAddressTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Auth::shouldUse('api-clients');
    }

    /**
     *
     */
    public function testIndex()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientDeliveryAddress $address */
        $address = ClientDeliveryAddress::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/delivery-addresses');

        $json->assertJson([
            'data' => [
                [
                    'uuid' => $address->uuid,
                    'title' => $address->title,
                    'city' => $address->city,
                    'street' => $address->street,
                    'house' => $address->house,
                    'floor' => $address->floor,
                    'entrance' => $address->entrance,
                    'apartment_number' => $address->apartment_number,
                    'intercom_code' => $address->intercom_code,

                    'created_at' => $address->created_at,
                    'updated_at' => $address->updated_at,
                ]
            ]
        ]);
    }

    /**
     *
     */
    public function testStore()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientDeliveryAddress $address */
        $address = ClientDeliveryAddress::factory()->makeOne();

        $body = Arr::except($address->getAttributes(), [
            'uuid',
            'client_uuid',
            'created_at',
            'updated_at',
        ]);
        $json = $this->be($self)->postJson('/clients/api/profile/delivery-addresses', $body);

        $json->assertSuccessful();
        $this->assertDatabaseHas('client_delivery_addresses', $body);
    }

    /**
     *
     */
    public function testUpdate()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientDeliveryAddress $address */
        $address = ClientDeliveryAddress::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);
        /** @var ClientDeliveryAddress $newAddress */
        $newAddress = ClientDeliveryAddress::factory()->makeOne();

        $body = Arr::except($newAddress->getAttributes(), [
            'uuid',
            'client_uuid',
            'created_at',
            'updated_at',
        ]);
        $json = $this->be($self)->putJson('/clients/api/profile/delivery-addresses/' . $address->uuid, $body);

        $body['uuid'] = $address->uuid;
        $json->assertSuccessful();
        $this->assertDatabaseHas('client_delivery_addresses', $body);
    }

    /**
     *
     */
    public function testShow()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientDeliveryAddress $address */
        $address = ClientDeliveryAddress::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->getJson('/clients/api/profile/delivery-addresses/' . $address->uuid);

        $json->assertJson([
            'data' => [
                'uuid' => $address->uuid,
            ]
        ]);
    }

    /**
     *
     */
    public function testDestroy()
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();
        /** @var ClientDeliveryAddress $address */
        $address = ClientDeliveryAddress::factory()->createOne([
            'client_uuid' => $self->uuid
        ]);

        $json = $this->be($self)->deleteJson('/clients/api/profile/delivery-addresses/' . $address->uuid);

        $json->assertJson([
            'data' => [
                'uuid' => $address->uuid,
            ]
        ]);

        $this->assertDatabaseMissing('client_delivery_addresses', [
            'uuid' => $address->uuid
        ]);
    }
}
