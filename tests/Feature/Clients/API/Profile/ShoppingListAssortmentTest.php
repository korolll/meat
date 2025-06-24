<?php

namespace Tests\Feature\Clients\API\Profile;

use App\Models\Assortment;
use App\Models\Client;
use App\Models\ClientShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCaseNotificationsFake;

class ShoppingListAssortmentTest extends TestCaseNotificationsFake
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
     * @param $name
     * @param $quantity
     * @param $quantityTwo
     *
     * @test
     * @testWith ["dummy", 49, 100]
     */
    public function store($name, $quantity, $quantityTwo)
    {
        $self = factory(Client::class)->create();
        $assortment = factory(Assortment::class)->create();
        $assortmentTwo = factory(Assortment::class)->create();
        $shoppingList = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid,
            'name' => $name,
        ]);

        $shoppingList->assortments()->attach($assortmentTwo, ['quantity' => $quantityTwo]);

        $json = $this->be($self)->postJson("/clients/api/profile/shopping-lists/{$shoppingList->uuid}/assortments", [
            'assortment_uuid' => $assortment->uuid,
            'quantity' => $quantity,
        ]);

        $json->assertSuccessful();

        $this->assertDatabaseHas('assortment_client_shopping_list', [
            'client_shopping_list_uuid' => $shoppingList->uuid,
            'assortment_uuid' => $assortment->uuid,
            'quantity' => $quantity,
        ]);

        $this->assertDatabaseHas('assortment_client_shopping_list', [
            'client_shopping_list_uuid' => $shoppingList->uuid,
            'assortment_uuid' => $assortmentTwo->uuid,
            'quantity' => $quantityTwo,
        ]);
    }

    /**
     * @param $name
     * @param $quantity
     *
     * @test
     * @testWith ["dummy", 49]
     */
    public function destroy($name, $quantity)
    {
        $self = factory(Client::class)->create();
        $assortment = factory(Assortment::class)->create();
        $assortmentTwo = factory(Assortment::class)->create();
        $shoppingList = factory(ClientShoppingList::class)->create([
            'client_uuid' => $self->uuid,
            'name' => $name,
        ]);

        $shoppingList->assortments()->attach($assortment, ['quantity' => $quantity]);
        $shoppingList->assortments()->attach($assortmentTwo, ['quantity' => 20]);

        $json = $this->be($self)->deleteJson(
            "/clients/api/profile/shopping-lists/{$shoppingList->uuid}/assortments/{$assortment->uuid}"
        );

        $json->assertSuccessful();

        $this->assertDatabaseMissing('assortment_client_shopping_list', [
            'client_shopping_list_uuid' => $shoppingList->uuid,
            'assortment_uuid' => $assortment->uuid,
            'quantity' => $quantity,
        ]);

        $this->assertDatabaseHas('assortment_client_shopping_list', [
            'client_shopping_list_uuid' => $shoppingList->uuid,
            'assortment_uuid' => $assortmentTwo->uuid,
            'quantity' => 20,
        ]);
    }
}
