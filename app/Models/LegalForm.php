<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalForm extends Model
{
    use SoftDeletes;

    /**
     * Индивидуальный предприниматель
     */
    const ID_IP = 'ip';

    /**
     * Общество с ограниченной ответственностью
     */
    const ID_OOO = 'ooo';

    /**
     * Закрытое акционерное общество
     */
    const ID_ZAO = 'zao';

    /**
     * Открытое акционерное общество
     */
    const ID_OAO = 'oao';

    /**
     * Публичное акционерное общество
     */
    const ID_PAO = 'pao';

    /**
     * Значения, которые доступны всем пользователям
     */
    const USER_AVAILABLE_IDS = [
        self::ID_IP,
        self::ID_OOO,
        self::ID_ZAO,
        self::ID_OAO,
        self::ID_PAO,
    ];

    /**
     * @var bool
     */
    public $incrementing = false;
}
