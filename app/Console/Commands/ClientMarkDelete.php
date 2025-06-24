<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class ClientMarkDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:mark-delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаляем помеченых на удаление пользователей';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = Carbon::now()->subDays(14)->format('Y-m-d');
        $this->info("Delete date from ${date}");

        Client::where('mark_deleted_at', '<', $date)->chunk(200, function ($clients) {
            foreach ($clients as $client) {

            $client->delete();
            $client->clientAuthenticationTokens()->delete();
            $client->clientPushTokens()->delete();
            $this->comment('Delete client: ' . $client->uuid);
            }
        });
        return 0;
    }
}
