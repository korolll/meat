<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GenerateUuidPrimary
{
    /**
     * @param Model $model
     */
    public function creating(Model $model)
    {
        $modelKey = $model->getKeyName();
        if (! $model->{$modelKey}) {
            $model->{$modelKey} = Str::orderedUuid()->toString();
        }
    }
}
