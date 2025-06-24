<?php

namespace Tests\Feature\API\Profile;

use App\Models\PriceList;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCaseNotificationsFake;

class PriceListTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function index()
    {
        $user = factory(User::class)->create();
        /** @var PriceList $priceList */
        $priceList = factory(PriceList::class)->create(['customer_user_uuid' => $user->uuid]);

        $self = $priceList->user;
        $json = $this->be($self)->getJson('/api/profile/price-lists');

        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $priceList->uuid,
                    'customer_user_uuid' => $user->uuid,
                    'customer_user_organization_name' => $user->organization_name,
                ],
            ],
        ]);
    }

    /**
     * @test
     * @testWith [true]
     *           [false]
     * @param bool $isPrivatePriceList
     */
    public function store(bool $isPrivatePriceList)
    {
        $priceList = $isPrivatePriceList ? factory(PriceList::class)->state('private')->make() : factory(PriceList::class)->make();

        $self = $priceList->user;
        $json = $this->be($self)->postJson('/api/profile/price-lists', $priceList->only([
            'name',
            'customer_user_uuid'
        ]));

        $data = [
            'uuid' => $json->json('data.uuid'),
            'name' => $priceList->name,
            'customer_user_uuid' => $priceList->customer_user_uuid,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('price_lists', $data);
    }

    /**
     * @test
     */
    public function show()
    {
        $priceList = factory(PriceList::class)->create();

        $self = $priceList->user;
        $json = $this->be($self)->getJson("/api/profile/price-lists/{$priceList->uuid}");

        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $priceList->uuid,
                'customer_user_uuid' => $priceList->customer_user_uuid,
            ],
        ]);
    }

    /**
     * @test
     * @testWith [true]
     *           [false]
     * @param bool $isPrivatePriceList
     */
    public function update(bool $isPrivatePriceList)
    {
        if ($isPrivatePriceList) {
            $priceListOld = factory(PriceList::class)->state('private')->create();
        } else {
            $priceListOld = factory(PriceList::class)->create();
        }

        $priceListNew = factory(PriceList::class)->make([
            'customer_user_uuid' => $priceListOld->customer_user_uuid
        ]);

        $self = $priceListOld->user;
        $json = $this->be($self)->putJson("/api/profile/price-lists/{$priceListOld->uuid}", $priceListNew->only([
            'name',
        ]));

        $data = [
            'uuid' => $priceListOld->uuid,
            'name' => $priceListNew->name,
            'customer_user_uuid' => $priceListOld->customer_user_uuid,
        ];

        $json->assertSuccessful()->assertJson(compact('data'));
        $this->assertDatabaseHas('price_lists', $data);
    }

    /**
     * @test
     */
    public function destroy()
    {
        $priceList = factory(PriceList::class)->create();

        $self = $priceList->user;
        $json = $this->be($self)->deleteJson("/api/profile/price-lists/{$priceList->uuid}");

        $json->assertSuccessful();
    }

    /**
     * @test
     */
    public function export()
    {
        Excel::fake();

        $priceList = factory(PriceList::class)->create();

        $self = $priceList->user;
        $json = $this->be($self)->getJson("/api/profile/price-lists/{$priceList->uuid}/export/xlsx");

        $json->assertSuccessful();

        Excel::assertDownloaded('price_list.xlsx');
    }
}
