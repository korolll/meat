<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratoryTestStatus extends Model
{
    /**
     * Создана (черновик)
     */
    const ID_CREATED = 'created';

    /**
     * Новая
     */
    const ID_NEW = 'new';

    /**
     * В работе
     */
    const ID_IN_WORK = 'in-work';

    /**
     * Выполнена
     */
    const ID_DONE = 'done';

    /**
     * Отменена
     */
    const ID_CANCELED = 'canceled';

    /**
     * @var bool
     */
    public $incrementing = false;
}
