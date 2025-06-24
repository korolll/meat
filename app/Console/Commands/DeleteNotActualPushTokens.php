<?php

namespace App\Console\Commands;

use App\Models\ClientPushToken;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class DeleteNotActualPushTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push-tokens:delete-not-actual
          {--bulk-size=100 : Delete bulk size}
    ';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаление не актуальных пуш токенов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ClientPushToken::orderBy('client_uuid')
            ->orderByDesc('updated_at')
            ->distinct(['client_uuid'])
            ->chunk((int)$this->option('bulk-size'), function (Collection $tokens) {
                $this->processTokens($tokens);
            });
    }

    protected function processTokens(Collection $tokens): void
    {
        $clientUuids = [];
        $tokenIds = [];
        /** @var ClientPushToken $token */
        foreach ($tokens as $token) {
            $clientUuids[] = $token->client_uuid;
            $tokenIds[] = $token->id;
        }

        ClientPushToken::query()
            ->whereNotIn('id', $tokenIds)
            ->whereIn('client_uuid', $clientUuids)
            ->delete();
    }
}
