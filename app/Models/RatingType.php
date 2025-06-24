<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatingType extends Model
{
    use SoftDeletes;

    /**
     * Рейтинг, просто рейтинг
     */
    const ID_COMMON = 'common';

    /**
     * Рейтинг покупателя
     */
    const ID_CUSTOMER = 'customer';

    /**
     * Рейтинг поставщика
     */
    const ID_SUPPLIER = 'supplier';

    /**
     * @var bool
     */
    public $incrementing = false;
}
