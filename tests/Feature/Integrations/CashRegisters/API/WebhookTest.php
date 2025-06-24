<?php

namespace Tests\Feature\Integrations\CashRegisters\API;

use App\Models\Product;
use App\Models\User;
use App\Services\Integrations\Iiko\IikoClientInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCaseNotificationsFake;

class WebhookTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Чтобы отключить проверку токена
        Config::set('app.integrations.cash-registers.token', null);
    }

    /**
     *
     */
    public function testUpdateStopList()
    {
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();

        /** @var Product $product1 */
        $product1 = factory(Product::class)->create([
            'user_uuid' => $store->uuid,
            'quantity' => 100
        ]);

        /** @var Product $product2 */
        $product2 = factory(Product::class)->create([
            'user_uuid' => $store->uuid,
            'quantity' => 0
        ]);

        $client = $this->createMock(IikoClientInterface::class);
        $this->app->instance(IikoClientInterface::class, $client);

        $client
            ->method('getStopListsMap')
            ->with([$store->uuid])
            ->willReturn([
                $store->uuid => [
                    $product1->assortment_uuid => 0
                ]
            ]);

        $data = [
            'organizationId' => $store->uuid
        ];
        $json = $this->postJson('/integrations/cash-registers/api/webhook/update-stop-list', $data);
        $json->assertSuccessful();

        $product1->refresh();
        self::assertEquals(0, $product1->quantity);

        $product2->refresh();
        self::assertEquals(9999, $product2->quantity);
    }
}
