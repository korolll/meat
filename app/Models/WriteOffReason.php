<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WriteOffReason extends Model
{
    use SoftDeletes;

    /**
     * Дегустация
     */
    const ID_TASTING = 'tasting';

    /**
     * Истёк срок годности
     */
    const ID_SHELF_LIFE = 'shelf-life';

    /**
     * Списание по качеству
     */
    const ID_QUALITY = 'quality';

    /**
     * Списание по бою
     */
    const ID_BROKEN = 'broken';

    /**
     * Списание неправ. хранение
     */
    const ID_IMPROPER_STORAGE = 'improper-storage';

    /**
     * Разукомплектация (расх компл)
     */
    const ID_DISMANTLING = 'dismantling';

    /**
     * Отмена Поставки со склада
     */
    const ID_CANCELLATION_DELIVERY = 'cancellation-delivery';

    /**
     * Расход по перемещению товара
     */
    const ID_MOVEMENT = 'movement';

    /**
     * Минус корректировка товара
     */
    const ID_CORRECTION = 'correction';

    /**
     * @var bool
     */
    public $incrementing = false;
}
