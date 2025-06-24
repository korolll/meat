<?php

namespace Tests\Feature\API;

use App\Models\MealReceiptTab;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Tests\TestCaseNotificationsFake;

class MealReceiptTabTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceiptTab $mealReceiptTab */
        $mealReceiptTab = MealReceiptTab::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [[
            'uuid' => $mealReceiptTab->uuid,
            'meal_receipt_uuid' => $mealReceiptTab->meal_receipt_uuid,
            'title' => $mealReceiptTab->title,
        ]];
        $response = $this->be($self, 'api')->json('get', '/api/meal-receipt-tabs', $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testShow(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceiptTab $mealReceiptTab */
        $mealReceiptTab = MealReceiptTab::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [
            'uuid' => $mealReceiptTab->uuid,
            'meal_receipt_uuid' => $mealReceiptTab->meal_receipt_uuid,
            'title' => $mealReceiptTab->title,
        ];
        $response = $this->be($self, 'api')->json('get', '/api/meal-receipt-tabs/' . $mealReceiptTab->uuid, $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testCreate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceiptTab $mealReceiptTab */
        $mealReceiptTab = MealReceiptTab::factory()->makeOne();

        // Next test collection
        $data = [
            'meal_receipt_uuid' => $mealReceiptTab->meal_receipt_uuid,
            'title' => $mealReceiptTab->title,
            'text' => $mealReceiptTab->text,
            'text_color' => $mealReceiptTab->text_color,
            'duration' => $mealReceiptTab->duration,
            'sequence' => $mealReceiptTab->sequence,
            'button_title' => $mealReceiptTab->button_title,
            'file_uuid' => $mealReceiptTab->file->uuid,
        ];

        $response = $this->be($self, 'api')->json('post', '/api/meal-receipt-tabs', $data);
        $response->assertSuccessful()->assertJson([
            'data' => Arr::except($data, ['file_uuid'])
        ]);
        $this->assertDatabaseHas('meal_receipt_tabs', $data);
    }

    /**
     *
     */
    public function testUpdate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceiptTab $mealReceiptTabOld */
        $mealReceiptTabOld = MealReceiptTab::factory()->createOne();
        /** @var MealReceiptTab $mealReceiptTabNew */
        $mealReceiptTabNew = MealReceiptTab::factory()->makeOne();

        // Next test collection
        $data = [
            'meal_receipt_uuid' => $mealReceiptTabNew->meal_receipt_uuid,
            'title' => $mealReceiptTabNew->title,
            'text' => $mealReceiptTabNew->text,
            'text_color' => $mealReceiptTabNew->text_color,
            'duration' => $mealReceiptTabNew->duration,
            'sequence' => $mealReceiptTabNew->sequence,
            'button_title' => $mealReceiptTabNew->button_title,
            'file_uuid' => $mealReceiptTabNew->file->uuid,
        ];
        $response = $this->be($self, 'api')->json('put', '/api/meal-receipt-tabs/' . $mealReceiptTabOld->uuid, $data);
        $response->assertSuccessful()->assertJson([
            'data' => Arr::except($data, ['file_uuid'])
        ]);
        $this->assertDatabaseHas('meal_receipt_tabs', $data);
    }

    /**
     *
     */
    public function testDelete(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var MealReceiptTab $mealReceiptTabOld */
        $mealReceiptTab = MealReceiptTab::factory()->createOne();
        $response = $this->be($self, 'api')->json('delete', '/api/meal-receipt-tabs/' . $mealReceiptTab->uuid);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $mealReceiptTab->refresh();
        $this->assertSoftDeleted($mealReceiptTab);
    }
}
