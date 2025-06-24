<?php

namespace Tests\Feature\Clients\API;

use App\Models\Client;
use App\Models\MealReceipt;
use App\Models\MealReceiptTab;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MealReceiptTest extends TestCase
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
    public function testIndex(): void
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var MealReceiptTab $mealReceiptTab */
        $mealReceiptTab = MealReceiptTab::factory()->createOne();
        $mealReceipt = $mealReceiptTab->mealReceipt;

        DB::table('client_meal_receipt_likes')->insert([
            'client_uuid' => $self->uuid,
            'meal_receipt_uuid' => $mealReceipt->uuid,
            'is_positive' => true
        ]);

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [[
            'uuid' => $mealReceipt->uuid,
            'name' => $mealReceipt->name,
            'file_path' => Storage::url($mealReceipt->file->path),
            'client_like_value' => true,

            'tabs' => [[
                'uuid' => $mealReceiptTab->uuid,
                'title' => $mealReceiptTab->title,
                'text' => $mealReceiptTab->text,

                'text_color' => $mealReceiptTab->text_color,
                'duration' => $mealReceiptTab->duration,
                'button_title' => $mealReceiptTab->button_title,
                'url' => $mealReceiptTab->url,
                'file_path' => Storage::url($mealReceiptTab->file->path),
            ]]
        ]];
        $response = $this->be($self)->json('get', '/clients/api/meal-receipts', $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testIndexUniqueSections(): void
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();

        MealReceipt::factory()->createOne([
            'section' => '123'
        ]);
        MealReceipt::factory()->createOne([
            'section' => '123'
        ]);
        MealReceipt::factory()->createOne([
            'section' => '321'
        ]);

        $response = $this->be($self)->json('get', '/clients/api/meal-receipts-unique-sections');
        $response->assertSuccessful()->assertJson([
            'data' => [
                ['section' => '123'],
                ['section' => '321'],
            ]
        ]);
    }

    /**
     *
     */
    public function testShow(): void
    {
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var MealReceiptTab $mealReceiptTab */
        $mealReceiptTab = MealReceiptTab::factory()->createOne();
        $mealReceipt = $mealReceiptTab->mealReceipt;

        $data = [
            'uuid' => $mealReceipt->uuid,
            'name' => $mealReceipt->name,
            'file_path' => Storage::url($mealReceipt->file->path),

            'tabs' => [[
                'uuid' => $mealReceiptTab->uuid,
                'title' => $mealReceiptTab->title,
                'text' => $mealReceiptTab->text,

                'text_color' => $mealReceiptTab->text_color,
                'duration' => $mealReceiptTab->duration,
                'button_title' => $mealReceiptTab->button_title,
                'url' => $mealReceiptTab->url,
                'file_path' => Storage::url($mealReceiptTab->file->path),
            ]]
        ];
        $response = $this->be($self)->json('get', '/clients/api/meal-receipts/' . $mealReceipt->uuid);
        $response->assertSuccessful()->assertJson(compact('data'));
        $mealReceipt->refresh();
    }

    /**
     *
     */
    public function testReaction(): void
    {
        /** @var Client $client */
        $client = factory(Client::class)->create();

        /** @var MealReceiptTab $mealReceiptTab */
        $mealReceiptTab = MealReceiptTab::factory()->createOne();
        /** @var MealReceiptTab $mealReceiptTab2 */
        $mealReceiptTab2 = MealReceiptTab::factory()->createOne();
        $mealReceipt = $mealReceiptTab->mealReceipt;
        $mealReceipt2 = $mealReceiptTab2->mealReceipt;

        $body = [
            'is_positive' => true
        ];
        $response = $this->be($client)->json('post', '/clients/api/meal-receipts/' . $mealReceipt->uuid . '/reaction', $body);
        $response->assertSuccessful();

        $this->assertDatabaseHas('client_meal_receipt_likes', [
            'client_uuid' => $client->uuid,
            'meal_receipt_uuid' => $mealReceipt->uuid,
            'is_positive' => $body['is_positive'],
        ]);

        $body = [
            'is_positive' => false
        ];
        $response = $this->be($client)->json('post', '/clients/api/meal-receipts/' . $mealReceipt->uuid . '/reaction', $body);
        $response->assertSuccessful();

        $this->assertDatabaseHas('client_meal_receipt_likes', [
            'client_uuid' => $client->uuid,
            'meal_receipt_uuid' => $mealReceipt->uuid,
            'is_positive' => $body['is_positive'],
        ]);

        $body = [
            'is_positive' => false
        ];
        $response = $this->be($client)->json('post', '/clients/api/meal-receipts/' . $mealReceipt2->uuid . '/reaction', $body);
        $response->assertSuccessful();

        $this->assertDatabaseHas('client_meal_receipt_likes', [
            'client_uuid' => $client->uuid,
            'meal_receipt_uuid' => $mealReceipt2->uuid,
            'is_positive' => $body['is_positive'],
        ]);
    }
}
