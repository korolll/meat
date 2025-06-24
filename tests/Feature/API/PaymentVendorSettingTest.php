<?php

namespace Tests\Feature\API;

use App\Models\PaymentVendorSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCaseNotificationsFake;

class PaymentVendorSettingTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var User $store1 */
        $store1 = factory(User::class)->state('store')->create();
        /** @var User $store2 */
        $store2 = factory(User::class)->state('store')->create();

        /** @var PaymentVendorSetting $vendor */
        $vendor = PaymentVendorSetting::factory()->createOne();
        $vendor->users()->sync([
            $store1->uuid => ['is_active' => true],
            $store2->uuid => ['is_active' => false],
        ]);

        $json = $this->be($self)->getJson('/api/payment-vendor-settings');
        $json->assertSuccessful()->assertJson([
            'data' => [
                [
                    'uuid' => $vendor->uuid,
                    'payment_vendor_id' => $vendor->payment_vendor_id,
                    'config' => $vendor->config,
                    'stores' => [
                        ['store_uuid' => $store1->uuid, 'is_active' => true],
                        ['store_uuid' => $store2->uuid, 'is_active' => false],
                    ]
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function testShow()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var PaymentVendorSetting $vendor */
        $vendor = PaymentVendorSetting::factory()->createOne();

        $json = $this->be($self)->getJson('/api/payment-vendor-settings/' . $vendor->uuid);
        $json->assertSuccessful()->assertJson([
            'data' => [
                'uuid' => $vendor->uuid,
                'payment_vendor_id' => $vendor->payment_vendor_id,
                'config' => $vendor->config,
                'stores' => []
            ]
        ]);
    }

    /**
     *
     */
    public function testStore()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var User $store */
        $store = factory(User::class)->state('store')->create();

        /** @var PaymentVendorSetting $vendor */
        $vendor = PaymentVendorSetting::factory()->makeOne();
        $data = [
            'payment_vendor_id' => $vendor->payment_vendor_id,
            'config' => $vendor->config,
            'stores' => [[
                'store_uuid' => $store->uuid,
                'is_active' => true,
            ]],
        ];

        $json = $this->be($self)->postJson("/api/payment-vendor-settings", $data);
        $json->assertSuccessful();
        $newUuid = $json->json('data.uuid');
        $newVendor = PaymentVendorSetting::findOrFail($newUuid);
        $this->assertEquals([
            'payment_vendor_id' => $vendor->payment_vendor_id,
            'config' => $vendor->config,
        ], [
            'payment_vendor_id' => $newVendor->payment_vendor_id,
            'config' => $newVendor->config,
        ]);

        $this->assertDatabaseHas('payment_vendor_setting_user', [
            'payment_vendor_setting_uuid' => $newUuid,
            'user_uuid' => $store->uuid,
            'is_active' => true
        ]);
    }

    /**
     *
     */
    public function testUpdate()
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();
        /** @var User $store1 */
        $store1 = factory(User::class)->state('store')->create();
        /** @var User $store2 */
        $store2 = factory(User::class)->state('store')->create();
        /** @var User $store3 */
        $store3 = factory(User::class)->state('store')->create();

        /** @var PaymentVendorSetting $vendor */
        $vendor = PaymentVendorSetting::factory()->createOne();
        $vendor->users()->sync([
            $store1->uuid => ['is_active' => true],
            $store2->uuid => ['is_active' => false],
        ]);

        /** @var PaymentVendorSetting $newVendor */
        $newVendor = PaymentVendorSetting::factory()->makeOne();
        $data = [
            'payment_vendor_id' => $newVendor->payment_vendor_id,
            'config' => $newVendor->config,
            'stores' => [
                [
                    'store_uuid' => $store2->uuid,
                    'is_active' => true,
                ],
                [
                    'store_uuid' => $store3->uuid,
                    'is_active' => true,
                ]
            ],
        ];

        $json = $this->be($self)->putJson("/api/payment-vendor-settings/" . $vendor->uuid, $data);
        $json->assertSuccessful();
        $vendor->refresh();
        $this->assertEquals([
            'payment_vendor_id' => $vendor->payment_vendor_id,
            'config' => $vendor->config,
        ], [
            'payment_vendor_id' => $newVendor->payment_vendor_id,
            'config' => $newVendor->config,
        ]);

        $this->assertDatabaseHas('payment_vendor_setting_user', [
            'payment_vendor_setting_uuid' => $vendor->uuid,
            'user_uuid' => $store2->uuid,
            'is_active' => true
        ]);
        $this->assertDatabaseHas('payment_vendor_setting_user', [
            'payment_vendor_setting_uuid' => $vendor->uuid,
            'user_uuid' => $store3->uuid,
            'is_active' => true
        ]);
        $this->assertDatabaseMissing('payment_vendor_setting_user', [
            'payment_vendor_setting_uuid' => $vendor->uuid,
            'user_uuid' => $store1->uuid
        ]);
    }
}
