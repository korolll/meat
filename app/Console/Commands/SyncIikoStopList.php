<?php

namespace App\Console\Commands;

use App\Jobs\UpdateProductStopListJob;
use App\Services\Integrations\Iiko\IikoClientInterface;
use Illuminate\Console\Command;

class SyncIikoStopList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iiko:sync-stop-lists';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизация стоп листов из iiko';

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var IikoClientInterface $client */
        $client = app(IikoClientInterface::class);
        $organizations = $client->getOrganizations();
        $organizationIds = [];
        foreach ($organizations as $organization) {
            $organizationIds[] = $organization['id'];
        }

        if ($organizationIds) {
            UpdateProductStopListJob::dispatch($organizationIds);
        }
    }
}
