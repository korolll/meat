<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssortmentPropertyDataType extends Model
{
    use SoftDeletes;

    /**
     * Тип данных: строка
     */
    const ID_STRING = 'string';

    /**
     * Тип данных: число
     */
    const ID_NUMBER = 'number';

    /**
     * Тип данных: перечисление
     */
    const ID_ENUM = 'enum';

    /**
     * @var bool
     */
    public $incrementing = false;
}
