<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRequestDeliveryStatus extends Model
{
    use SoftDeletes;

    /**
     * Новая
     */
    const ID_NEW = 'new';

    /**
     * Поиск исполнителя
     */
    const ID_WAITING = 'waiting';

    /**
     * В работе
     */
    const ID_IN_WORK = 'in-work';

    /**
     * @var bool
     */
    public $incrementing = false;
}
