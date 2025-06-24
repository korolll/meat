<?php

namespace Tests\Feature\API;

use App\Models\Assortment;
use App\Models\MealReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCaseNotificationsFake;

class MealReceiptTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceipt $mealReceipt */
        $mealReceipt = MealReceipt::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [[
            'uuid' => $mealReceipt->uuid,
            'name' => $mealReceipt->name,
        ]];
        $response = $this->be($self, 'api')->json('get', '/api/meal-receipts', $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testShow(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceipt $mealReceipt */
        $mealReceipt = MealReceipt::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [
            'uuid' => $mealReceipt->uuid,
            'name' => $mealReceipt->name,
        ];
        $response = $this->be($self, 'api')->json('get', '/api/meal-receipts/' . $mealReceipt->uuid, $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testCreate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceipt $mealReceipt */
        $mealReceipt = MealReceipt::factory()->makeOne();

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();

        // Next test collection
        $data = [
            'name' => $mealReceipt->name,
            'section' => $mealReceipt->section,
            'title' => $mealReceipt->title,
            'description' => $mealReceipt->description,
            'ingredients' => $mealReceipt->ingredients,
            'file_uuid' => $mealReceipt->file->uuid,
            'assortment_uuids' => [$assortment->uuid],
        ];
        $response = $this->be($self, 'api')->json('post', '/api/meal-receipts', $data);

        $response->assertSuccessful()->assertJson([
            'data' => [
                'name' => $mealReceipt->name,
                'section' => $mealReceipt->section,
                'title' => $mealReceipt->title,
                'description' => $mealReceipt->description,
                'ingredients' => $mealReceipt->ingredients,
                'file' => [
                    'uuid' => $mealReceipt->file->uuid,
                ],
                'assortment_uuids' => [$assortment->uuid],
            ]
        ]);
        $this->assertDatabaseHas('meal_receipts', [
            'name' => $mealReceipt->name,
            'section' => $mealReceipt->section,
            'title' => $mealReceipt->title,
            'description' => $mealReceipt->description,
            'file_uuid' => $mealReceipt->file->uuid,
        ]);
    }

    /**
     *
     */
    public function testUpdate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceipt $mealReceiptOld */
        $mealReceiptOld = MealReceipt::factory()->createOne();
        /** @var MealReceipt $mealReceiptNew */
        $mealReceiptNew = MealReceipt::factory()->makeOne();

        /** @var Assortment $assortment */
        $assortment = factory(Assortment::class)->create();

        // Next test collection
        $data = [
            'name' => $mealReceiptNew->name,
            'section' => $mealReceiptNew->section,
            'title' => $mealReceiptNew->title,
            'description' => $mealReceiptNew->description,
            'ingredients' => $mealReceiptNew->ingredients,
            'file_uuid' => $mealReceiptNew->file->uuid,
            'assortment_uuids' => [$assortment->uuid],
        ];
        $response = $this->be($self, 'api')->json('put', '/api/meal-receipts/' . $mealReceiptOld->uuid, $data);

        $response->assertSuccessful()->assertJson([
            'data' => [
                'name' => $mealReceiptNew->name,
                'section' => $mealReceiptNew->section,
                'title' => $mealReceiptNew->title,
                'description' => $mealReceiptNew->description,
                'ingredients' => $mealReceiptNew->ingredients,
                'file' => [
                    'uuid' => $mealReceiptNew->file->uuid,
                ],
                'assortment_uuids' => [$assortment->uuid],
            ]
        ]);
        $this->assertDatabaseHas('meal_receipts', [
            'name' => $mealReceiptNew->name,
            'section' => $mealReceiptNew->section,
            'title' => $mealReceiptNew->title,
            'description' => $mealReceiptNew->description,
            'file_uuid' => $mealReceiptNew->file->uuid,
        ]);
    }

    /**
     *
     */
    public function testDelete(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceipt $mealReceiptOld */
        $mealReceipt = MealReceipt::factory()->createOne();
        $response = $this->be($self, 'api')->json('delete', '/api/meal-receipts/' . $mealReceipt->uuid);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $mealReceipt->refresh();
        $this->assertSoftDeleted($mealReceipt);
    }
}
