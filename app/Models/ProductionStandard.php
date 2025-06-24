<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionStandard extends Model
{
    use SoftDeletes;

    /**
     * ГОСТ
     */
    const ID_GOST = 'gost';

    /**
     * ГОСТ
     */
    const ID_TU = 'tu';

    /**
     * @var bool
     */
    public $incrementing = false;
}
