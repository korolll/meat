<?php

namespace App\Console\Commands;

use App\Models\ClientCreditCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class DeleteNotBoundOldClientCreditCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:delete-old-cards';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удалить старые не привязанные карты клиента';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subDay = Date::today()->subDay();
        ClientCreditCard::where('created_at', '<=', $subDay)
            ->whereNull('binding_id')
            ->forceDelete();
    }
}
