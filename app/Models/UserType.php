<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserType extends Model
{
    use SoftDeletes;

    /**
     * Администратор
     */
    const ID_ADMIN = 'admin';

    /**
     * Поставщик
     */
    const ID_SUPPLIER = 'supplier';

    /**
     * Распределительный центр
     */
    const ID_DISTRIBUTION_CENTER = 'distribution-center';

    /**
     * Магазин
     */
    const ID_STORE = 'store';

    /**
     * Служба доставки
     */
    const ID_DELIVERY_SERVICE = 'delivery-service';

    /**
     * Лаборатория
     */
    const ID_LABORATORY = 'laboratory';

    /**
     * Значения, которые доступны всем пользователям
     */
    const USER_AVAILABLE_IDS = [
        self::ID_SUPPLIER,
        self::ID_DISTRIBUTION_CENTER,
        self::ID_STORE,
        self::ID_DELIVERY_SERVICE,
        self::ID_LABORATORY,
    ];

    /**
     * @var bool
     */
    public $incrementing = false;
}
