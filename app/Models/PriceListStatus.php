<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceListStatus extends Model
{
    use SoftDeletes;

    /**
     * Архивный
     */
    const ARCHIVE = 'archive';

    /**
     * Текущий
     */
    const CURRENT = 'current';

    /**
     * Будущий
     */
    const FUTURE = 'future';

    /**
     * @var bool
     */
    public $incrementing = false;
}
