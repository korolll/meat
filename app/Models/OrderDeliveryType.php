<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDeliveryType extends Model
{
    /**
     * Доставка
     */
    const ID_DELIVERY = 'delivery';

    /**
     * Самовывоз
     */
    const ID_PICKUP = 'pickup';

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

    const ALL = [
        self::ID_DELIVERY,
        self::ID_PICKUP,
    ];
}
