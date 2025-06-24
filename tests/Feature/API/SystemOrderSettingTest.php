<?php

namespace Tests\Feature\API;

use App\Models\SystemOrderSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class SystemOrderSettingTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex()
    {
        $self = factory(User::class)->state('admin')->create();

        SystemOrderSetting::query()->delete();
        SystemOrderSetting::query()->insert([
            'id' => SystemOrderSetting::ID_MIN_PRICE,
            'value' => '1234'
        ]);

        $json = $this->be($self)->getJson('/api/order-settings');
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'id' => SystemOrderSetting::ID_MIN_PRICE,
                    'value' => '1234',
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function testShow()
    {
        $self = factory(User::class)->state('admin')->create();

        SystemOrderSetting::query()->delete();
        SystemOrderSetting::query()->insert([
            'id' => SystemOrderSetting::ID_MIN_PRICE,
            'value' => '1234',
        ]);

        $json = $this->be($self)->getJson('/api/order-settings/' . SystemOrderSetting::ID_MIN_PRICE);
        $json->assertSuccessful()->assertJson([
            'data' => [
                'id' => SystemOrderSetting::ID_MIN_PRICE,
                'value' => '1234',
            ]
        ]);
    }

    /**
     *
     */
    public function testUpdate()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        $data = [
            'value' => '999999'
        ];

        $json = $this->be($self)->putJson("/api/order-settings/" . SystemOrderSetting::ID_MIN_PRICE, $data);
        $json->assertSuccessful();
        $this->assertDatabaseHas((new SystemOrderSetting)->getTable(), [
            'id' => SystemOrderSetting::ID_MIN_PRICE,
            'value' => '999999'
        ]);
    }
}
