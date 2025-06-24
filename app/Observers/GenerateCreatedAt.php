<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class GenerateCreatedAt
{
    /**
     * @param Model $model
     */
    public function creating(Model $model)
    {
        $model->{$model->getCreatedAtColumn()} = now();
    }
}
