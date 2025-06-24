<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPreRequestCustomerSupplier extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_pre_request_customer_supplier_relation';

    public $timestamps = false;
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = [
        'customer_user_uuid',
        'supplier_user_uuid'
    ];

    /**
     * @return BelongsTo
     */
    public function customerUser()
    {
        return $this->belongsTo(User::class, 'customer_user_uuid', 'uuid');
    }

    /**
     * @return BelongsTo
     */
    public function supplierUser()
    {
        return $this->belongsTo(User::class, 'supplier_user_uuid', 'uuid');
    }
}
