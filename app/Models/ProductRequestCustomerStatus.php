<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRequestCustomerStatus extends Model
{
    use SoftDeletes;

    /**
     * Новая
     */
    const ID_NEW = 'new';

    /**
     * В пути
     */
    const ID_ON_THE_WAY = 'on-the-way';

    /**
     * Выполнена
     */
    const ID_DONE = 'done';

    /**
     * Отменена заказчиком
     */
    const ID_USER_CANCELED = 'user-canceled';

    /**
     * На согласовании
     */
    const ID_MATCHING = 'matching';

    /**
     * @var bool
     */
    public $incrementing = false;
}
