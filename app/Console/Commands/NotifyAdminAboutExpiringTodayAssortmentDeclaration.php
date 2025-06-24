<?php

namespace App\Console\Commands;

use App\Models\Assortment;
use App\Models\User;
use App\Notifications\API\AssortmentDeclarationExpiringToday;
use Illuminate\Console\Command;

class NotifyAdminAboutExpiringTodayAssortmentDeclaration extends Command
{
    /**
     * @var string
     */
    protected $signature = 'assortment:declaration:expiring_today';

    /**
     * @var string
     */
    protected $description = 'Выполняет отправку уведомлений по номенклатурам, у которых срок декларация заканчивается сегодня';

    /**
     * @return void
     */
    public function handle()
    {
        $expiredAt = now()->format('d.m.Y');
        $admins = User::admin()->get();
        if ($admins->isEmpty()) {
            return;
        }

        Assortment::where('declaration_end_date', $expiredAt)->each(function (Assortment $assortment) use ($admins) {
            /** @var User $admin */
            foreach ($admins as $admin) {
                $admin->notify(new AssortmentDeclarationExpiringToday($assortment));
            }
        });
    }
}
