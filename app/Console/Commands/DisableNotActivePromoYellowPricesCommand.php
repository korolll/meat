<?php

namespace App\Console\Commands;

use App\Models\PromoYellowPrice;
use Illuminate\Console\Command;

class DisableNotActivePromoYellowPricesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'yellow-prices:disable-not-active';

    /**
     * @var string
     */
    protected $description = 'Выключает не активные желтые ценники';

    /**
     * @return void
     */
    public function handle()
    {
        PromoYellowPrice::enabled()
            ->where('end_at', '<', now())
            ->update([
                'is_enabled' => false
            ]);
    }
}
