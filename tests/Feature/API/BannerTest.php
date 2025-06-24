<?php

namespace Tests\Feature\API;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCaseNotificationsFake;

class BannerTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     *
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('must add testing data');
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Banner $banner */
        $banner = Banner::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [
            //TODO: Подобрать данные для отображения
        ];
        $response = $this->be($self, 'api')->json('get', '/api/banners', $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testShow(): void
    {
        $this->markTestIncomplete('must add testing data');
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Banner $banner */
        $banner = Banner::factory()->createOne();

        // Next test collection
        $query = [
            'page' => 1,
            'per_page' => 5,
        ];

        $data = [
            //TODO: Подобрать данные для отображения
        ];
        $response = $this->be($self, 'api')->json('get', '/api/banners/' . $banner->id, $query);
        $response->assertSuccessful()->assertJson(compact('data'));
    }

    /**
     *
     */
    public function testCreate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Banner $banner */
        $banner = Banner::factory()->createOne();

        // Next test collection
        $data = [
            'name' => $banner->name,
            'description' => $banner->description,
            'enabled' => $banner->enabled,
            'logo_file_uuid' => $banner->logoFile->uuid,
            'number' => $banner->number,
            'reference_type' => $banner->reference_type,
            'reference_uuid' => $banner->reference_uuid
        ];
        $response = $this->be($self, 'api')->json('post', '/api/banners', $data);

        $response->assertSuccessful()->assertJson([
            'data' => [
                'name' => $banner->name,
            ]
        ]);
        $this->assertDatabaseHas('banners', [
            'name' => $banner->name
        ]);
    }

    /**
     *
     */
    public function testUpdate(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Banner $bannerOld */
        $bannerOld = Banner::factory()->createOne();
        /** @var Banner $bannerNew */
        $bannerNew = Banner::factory()->makeOne();

        // Next test collection
        $data = [
            'name' => $bannerNew->name,
            'number' => $bannerNew->number,
            'description' => $bannerNew->description,
            'enabled' => $bannerNew->enabled,
            'logo_file_uuid' => $bannerNew->logoFile->uuid,
            'reference_type' => $bannerNew->reference_type,
            'reference_uuid' => $bannerNew->reference_uuid
        ];
        $response = $this->be($self, 'api')->json('put', '/api/banners/' . $bannerOld->id, $data);

        $response->assertSuccessful()->assertJson([
            'data' => [
                'name' => $bannerNew->name,
            ]
        ]);
        $this->assertDatabaseHas('banners', [
            'name' => $bannerNew->name
        ]);
    }

    /**
     *
     */
    public function testDelete(): void
    {
        /** @var User $self */
        $self = factory(User::class)->state('admin')->create();

        /** @var Banner $banner */
        $banner = Banner::factory()->createOne();
        $response = $this->be($self, 'api')->json('delete', '/api/banners/' . $banner->id);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $banner->refresh();
        $this->assertSoftDeleted($banner);
    }
}
