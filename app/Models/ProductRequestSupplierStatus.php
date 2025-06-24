<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRequestSupplierStatus extends Model
{
    use SoftDeletes;

    /**
     * Новая
     */
    const ID_NEW = 'new';
    /**
     * В работе
     */
    const ID_IN_WORK = 'in-work';
    /**
     * На согласовании
     */
    const ID_MATCHING = 'matching';
    /**
     * В пути
     */
    const ID_ON_THE_WAY = 'on-the-way';
    /**
     * Отменена заказчиком
     */
    const ID_USER_CANCELED = 'user-canceled';
    /**
     * Отказ поставщика
     */
    const ID_SUPPLIER_REFUSED = 'supplier-refused';
    /**
     * Выполнена
     */
    const ID_DONE = 'done';

    /**
     * @var bool
     */
    public $incrementing = false;
}
