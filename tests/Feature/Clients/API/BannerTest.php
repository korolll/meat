<?php

namespace Tests\Feature\Clients\API;

use App\Models\Banner;
use App\Models\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class BannerTest extends TestCase
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
        $this->markTestIncomplete('must add testing data');
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var Banner $banner */
        $banner = Banner::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [[
            //TODO: Подобрать данные для проверки
        ]];
        $response = $this->be($self)->json('get', '/clients/api/banners', $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testShow(): void
    {
        $this->markTestIncomplete('must add testing data');
        /** @var Client $self */
        $self = factory(Client::class)->create();

        /** @var Banner $banner */
        $banner = Banner::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [
            //TODO: Подобрать данные для проверки
        ];
        $response = $this->be($self)->json('get', '/clients/api/banners/' . $banner->id, $query);
        $response->assertSuccessful()->assertJson(compact('data'));
        $banner->refresh();
    }
}
