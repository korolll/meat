<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssortmentVerifyStatus extends Model
{
    use SoftDeletes;

    /**
     * Новый
     */
    const ID_NEW = 'new';

    /**
     * Подтверждён
     */
    const ID_APPROVED = 'approved';

    /**
     * Отклонён
     */
    const ID_DECLINED = 'declined';

    /**
     * @var bool
     */
    public $incrementing = false;
}
