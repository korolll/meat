<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemOrderSetting extends Model
{
    const ID_MIN_PRICE = 'min_price';

    const ID_DELIVERY_THRESHOLD = 'delivery_threshold';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var string
     */
    protected $keyType = 'string';

    protected $fillable = [
        'value'
    ];
}
