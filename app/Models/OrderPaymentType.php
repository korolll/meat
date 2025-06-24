<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentType extends Model
{
    /**
     * Оплата наличными
     */
    const ID_CASH = 'cash';

    /**
     * Онлайн оплата
     */
    const ID_ONLINE = 'online';

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
