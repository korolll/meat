<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\WriteOff;
use App\Models\WriteOffReason;
use App\Services\Integrations\Iiko\IikoClientInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateProductStopListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private array $organizationIds;

    /**
     * @param array $organizationIds
     */
    public function __construct(array $organizationIds)
    {
        $this->organizationIds = $organizationIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var IikoClientInterface $client */
        $client = app(IikoClientInterface::class);
        $stopList = $client->getStopListsMap($this->organizationIds);
        foreach ($stopList as $orgId => $list) {
            $this->handleStopList($orgId, $list);
        }
    }

    /**
     * @param string $orgId
     * @param array  $list
     *
     * @return void
     */
    protected function handleStopList(string $orgId, array $list): void
    {
        $organization = User::findOrFail($orgId);
        $assortmentIds = array_keys($list);

        $makeZeroQuantityProducts = $organization->products()
            ->where('quantity', '>', 0)
            ->whereIn('assortment_uuid', $assortmentIds)
            ->get();

        /** @var \App\Models\Product $product */
        foreach ($makeZeroQuantityProducts as $product) {
            $writeOff = new WriteOff();
            $writeOff->user()->associate($organization);
            $writeOff->product()->associate($product);
            $writeOff->quantity_delta = -$product->quantity;
            $writeOff->write_off_reason_id = WriteOffReason::ID_MOVEMENT;
            $writeOff->save();
        }
    }
}
