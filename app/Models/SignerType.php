<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SignerType extends Model
{
    use SoftDeletes;

    /**
     * Генеральный директор
     */
    const ID_GENERAL_DIRECTOR = 'general_director';

    /**
     * Доверенное лицо
     */
    const ID_CONFIDANT = 'confidant';

    /**
     * @var bool
     */
    public $incrementing = false;
}
