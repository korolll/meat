<?php

namespace Tests\Feature\API;

use App\Models\DiscountForbiddenAssortment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class DiscountForbiddenAssortmentTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function index()
    {
        /** @var DiscountForbiddenAssortment $assortment */
        $assortment = DiscountForbiddenAssortment::factory()->createOne();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson('/api/discount-forbidden-assortments?per_page=1000');

        $json->assertSuccessful()->assertJson([
            'data' => [[
                'uuid' => $assortment->uuid,
                'assortment' => [
                    'uuid' => $assortment->assortment_uuid
                ]
            ]]
        ]);
    }

    /**
     * @test
     */
    public function store()
    {
        /** @var DiscountForbiddenAssortment $assortment */
        $assortment = DiscountForbiddenAssortment::factory()->makeOne();

        $self = factory(User::class)->state('admin')->create();
        $data = [
            'assortment_uuid' => $assortment->assortment_uuid
        ];
        $json = $this->be($self)->postJson('/api/discount-forbidden-assortments', $data);

        $json->assertSuccessful()->assertJson([
            'data' => $data
        ]);

        $this->assertDatabaseHas($assortment->getTable(), $data);
    }

    /**
     * @test
     */
    public function storeBulk()
    {
        /** @var DiscountForbiddenAssortment $assortment1 */
        $assortment1 = DiscountForbiddenAssortment::factory()->makeOne();
        /** @var DiscountForbiddenAssortment $assortment2 */
        $assortment2 = DiscountForbiddenAssortment::factory()->makeOne();

        $self = factory(User::class)->state('admin')->create();
        $data = [
            'assortment_uuids' => [
                $assortment1->assortment_uuid,
                $assortment2->assortment_uuid,
            ]
        ];
        $json = $this->be($self)->postJson('/api/discount-forbidden-assortments/store-bulk', $data);

        $json->assertSuccessful()->assertJson([
            'data' => [
                ['assortment_uuid' => $assortment1->assortment_uuid],
                ['assortment_uuid' => $assortment2->assortment_uuid],
            ]
        ]);

        $this->assertDatabaseHas($assortment1->getTable(), [
            'assortment_uuid' => $assortment1->assortment_uuid
        ]);
        $this->assertDatabaseHas($assortment2->getTable(), [
            'assortment_uuid' => $assortment2->assortment_uuid
        ]);
    }

    /**
     * @test
     */
    public function show()
    {
        /** @var DiscountForbiddenAssortment $assortment */
        $assortment = DiscountForbiddenAssortment::factory()->createOne();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->getJson("/api/discount-forbidden-assortments/{$assortment->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $assortment->uuid,
            ],
        ]);
    }

    /**
     * @test
     */
    public function destroy()
    {
        /** @var DiscountForbiddenAssortment $assortment */
        $assortment = DiscountForbiddenAssortment::factory()->createOne();

        $self = factory(User::class)->state('admin')->create();
        $json = $this->be($self)->deleteJson("/api/discount-forbidden-assortments/{$assortment->uuid}");
        $json->assertSuccessful();
        $this->assertDatabaseMissing($assortment->getTable(), ['assortment_uuid' => $assortment->assortment_uuid]);
    }
}
