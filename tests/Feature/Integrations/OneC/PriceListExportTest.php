<?php

namespace Tests\Feature\Integrations\OneC;

use App\Events\PriceListReadyForExport1C;
use App\Jobs\ExportPriceListTo1C;
use App\Models\PriceList;
use App\Models\PriceListStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCaseNotificationsFake;

class PriceListExportTest extends TestCaseNotificationsFake
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function priceListReadyForExport1CEventDispatched()
    {
        Event::fake([PriceListReadyForExport1C::class]);

        /** @var PriceList $priceList */
        $priceList = factory(PriceList::class)->create(['date_from' => now()->subMinutes(5)]);
        $priceList->price_list_status_id = PriceListStatus::CURRENT;
        $priceList->save();

        Event::assertDispatched(PriceListReadyForExport1C::class, 1);
    }

    /**
     * @test
     */
    public function exportPriceListTo1CJobQueued()
    {
        Queue::fake();
        /** @var PriceList $priceList */
        $priceList = factory(PriceList::class)->create();

        Config::set('services.1c.users_allowed_to_export', $priceList->user_uuid);
        Config::set('services.1c.price_list_exporter.uri', 'test_uri');

        $priceList->price_list_status_id = PriceListStatus::CURRENT;
        $priceList->save();

        Queue::assertPushed(ExportPriceListTo1C::class, 1);
    }

    /**
     * @test
     */
    public function exportPriceListTo1CJobNotQueuedWithNotAllowedUser()
    {
        Queue::fake();
        /** @var PriceList $priceList */
        $priceList = factory(PriceList::class)->create();
        $any_user = factory(User::class)->create();

        Config::set('services.1c.users_allowed_to_export', $any_user->uuid);
        Config::set('services.1c.price_list_exporter.uri', 'test_uri');

        $priceList->price_list_status_id = PriceListStatus::CURRENT;
        $priceList->save();

        Queue::assertNotPushed(ExportPriceListTo1C::class);
    }

    /**
     * @test
     */
    public function exportPriceListTo1CJobNotQueuedWithout1CUri()
    {
        Queue::fake();
        /** @var PriceList $priceList */
        $priceList = factory(PriceList::class)->create();

        Config::set('services.1c.users_allowed_to_export', $priceList->uuid);
        Config::set('services.1c.price_list_exporter.uri', null);

        $priceList->price_list_status_id = PriceListStatus::CURRENT;
        $priceList->save();

        Queue::assertNotPushed(ExportPriceListTo1C::class);
    }
}
