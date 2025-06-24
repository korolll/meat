<?php

namespace App\Console\Commands;

use App\Models\Assortment;
use App\Models\User;
use App\Notifications\API\AssortmentExpirationOfDeclaration;
use Illuminate\Console\Command;

class NotifyAdminAboutExpirationOfAssortmentDeclaration extends Command
{
    /**
     * @var string
     */
    protected $signature = 'assortment:declaration:expiration_soon';

    /**
     * @var string
     */
    protected $description = 'Выполняет отправку уведомлений по номенклатурам, у которых скоро истечер срок декларации';

    /**
     * @return void
     */
    public function handle()
    {
        $days = config('app.assortments.notifications.declaration.notify_when_x_days_left');
        $expiredAt = now()->addDays($days)->format('d.m.Y');
        $admins = User::admin()->get();
        if ($admins->isEmpty()) {
            return;
        }

        Assortment::where('declaration_end_date', $expiredAt)->each(function (Assortment $assortment) use ($admins) {
            /** @var User $admin */
            foreach ($admins as $admin) {
                $admin->notify(new AssortmentExpirationOfDeclaration($assortment));
            }
        });
    }
}
