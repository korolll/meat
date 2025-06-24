<?php

namespace App\Observers;

use App\Models\Stocktaking;

class StocktakingObserver
{
    /**
     * @param \App\Models\Stocktaking $model
     */
    public function updating(Stocktaking $model)
    {
        $wasApproved = $model->getOriginal('approved_at') !== null;
        $nowApproved = $model->is_approved;

        if (! $wasApproved && $nowApproved) {
            app('warehouse.transactions.providers.stocktaking')->produce($model);
            $this->deleteOldStocktakings($model);
        }
    }

    /**
     * @param \App\Models\Stocktaking $stocktaking
     */
    protected function deleteOldStocktakings(Stocktaking $stocktaking): void
    {
        $query = Stocktaking::whereUserUuid($stocktaking->user_uuid)
            ->where('uuid', '!=', $stocktaking->uuid)
            ->whereNotNull('approved_at')
            ->orderByDesc('approved_at');

        $skip = 1;
        $counter = 0;
        $query->each(function (Stocktaking $oldStocktaking) use ($skip, &$counter) {
            $counter++;
            if ($counter <= $skip) {
                return;
            }

            $this->deleteStocktaking($oldStocktaking);
        });
    }

    /**
     * @param \App\Models\Stocktaking $stocktaking
     */
    protected function deleteStocktaking(Stocktaking $stocktaking): void
    {
        $stocktaking->products()->detach();
        $stocktaking->forceDelete();
    }
}
