<?php

namespace App\Observers;

use App\Models\WriteOff;

class WriteOffObserver
{
    /**
     * @param \App\Models\WriteOff $model
     */
    public function creating(WriteOff $model)
    {
        app('warehouse.transactions.providers.write-off')->produce($model);
    }
}
