<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentVendor extends Model
{
    const ID_SBERBANK = 'sberbank';

    const ID_YOOKASSA = 'yookassa';

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

    protected $fillable = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('config');
    }
}
