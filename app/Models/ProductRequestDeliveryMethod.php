<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRequestDeliveryMethod extends Model
{
    use SoftDeletes;

    /**
     * Доставка
     */
    const ID_DELIVERY = 'delivery';

    /**
     * Самовывоз
     */
    const ID_SELF_DELIVERY = 'self-delivery';

    /**
     * @var bool
     */
    public $incrementing = false;
}
