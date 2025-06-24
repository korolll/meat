<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportationPointType extends Model
{
    use SoftDeletes;

    /**
     * Точка погрузки
     */
    const ID_LOADING = 'loading';

    /**
     * Точка разгрузки
     */
    const ID_UNLOADING = 'unloading';

    /**
     * @var bool
     */
    public $incrementing = false;
}
