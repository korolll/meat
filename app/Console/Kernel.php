<?php

namespace App\Console;

use App\Jobs\DeleteExpiredClientPurchase;
use App\Jobs\UpdateUsersCachedProductsCountInCatalogs;
use App\Services\Management\Promos\DiverseFood\CalculatePromoDiverseFoodCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * @var string[]
     */
    protected $commands = [
        CalculatePromoDiverseFoodCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('auth:clear-resets')->hourly();
        $schedule->job(UpdateUsersCachedProductsCountInCatalogs::class)->hourly();
        $schedule->job(DeleteExpiredClientPurchase::class)->dailyAt('00:10');

        $schedule->command('receipts:delete-old-without-card')->hourly();
        $schedule->command('price-list:rotate')->everyMinute();
        $schedule->command('notification-tasks:execute')->everyMinute();
        // $schedule->command('loyalty-card:update-discount')->monthly();
        // $schedule->command('laboratory-test:cancel-old')->everyMinute();
        $schedule->command('file:delete-unused')->dailyAt('4:00');
        // $schedule->command('assortment:declaration:expiration_soon')->dailyAt('12:00');
        // $schedule->command('assortment:declaration:expiring_today')->dailyAt('12:00');
        // $schedule->command('assortment:declaration:expired')->dailyAt('12:00')->when(function () {
        //    $frequency = config('app.assortments.notifications.declaration.notify_about_expired_each_x_days');
        //   return now()->dayOfYear % $frequency === 0;
        //});
        // $schedule->command('product-pre-request:create-supplier-request')->hourlyAt('58');
        // $schedule->command('product-pre-request:clear-temp-table')->dailyAt('23:55');
        $schedule->command('promo-diverse-food:calculate')->monthlyOn();
        $schedule->command('yellow-prices:disable-not-active')->everyFifteenMinutes();
        $schedule->command('client:mark-delete')->daily()->runInBackground();


        // Синхронизация iiko данных
        $schedule->command('iiko:sync')->dailyAt('01:10');

        // Обработка iiko стоп листов
        $schedule->command('iiko:sync-stop-lists')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        // require base_path('routes/console.php');
    }
}
