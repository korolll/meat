<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssortmentUnit extends Model
{
    use SoftDeletes;

    /**
     * Упаковка
     */
    const ID_PACKAGE = 'package';

    /**
     * Штука
     */
    const ID_PIECE = 'piece';

    /**
     * Рулон
     */
    const ID_ROLL = 'roll';

    /**
     * Набор
     */
    const ID_SET = 'set';

    /**
     * Килограмм
     */
    const ID_KILOGRAM = 'kilogram';

    /**
     * @var bool
     */
    public $incrementing = false;
}
