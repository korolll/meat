<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportationStatus extends Model
{
    use SoftDeletes;

    /**
     * Новый
     */
    const ID_NEW = 'new';

    /**
     * В пути
     */
    const ID_ON_THE_WAY = 'on-the-way';

    /**
     * Заверщён
     */
    const ID_DONE = 'done';

    /**
     * @var bool
     */
    public $incrementing = false;
}
