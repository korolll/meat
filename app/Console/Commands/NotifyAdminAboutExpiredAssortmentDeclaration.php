<?php

namespace App\Console\Commands;

use App\Models\Assortment;
use App\Models\User;
use App\Notifications\API\AssortmentsDeclarationExpired;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class NotifyAdminAboutExpiredAssortmentDeclaration extends Command
{
    /**
     * @var string
     */
    protected $signature = 'assortment:declaration:expired';

    /**
     * @var string
     */
    protected $description = 'Выполняет отправку уведомлений по номенклатурам, у которых истек срок декларации';

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

        Assortment::where('declaration_end_date', '<', $expiredAt)->chunk(1000, function (Collection $assortments) use ($admins) {
            /** @var User $admin */
            foreach ($admins as $admin) {
                $admin->notify(new AssortmentsDeclarationExpired($assortments));
            }
        });
    }
}
