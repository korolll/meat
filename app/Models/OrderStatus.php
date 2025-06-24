<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    /**
     * Оформлен
     */
    const ID_NEW = 'new';

    /**
     * Собирается
     */
    const ID_COLLECTING = 'collecting';

    /**
     * Собран
     */
    const ID_COLLECTED = 'collected';

    /**
     * Доставляется
     */
    const ID_DELIVERING = 'delivering';

    /**
     * Выполнен
     */
    const ID_DONE = 'done';

    /**
     * Отменен
     */
    const ID_CANCELLED = 'cancelled';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $keyType = 'string';
}
