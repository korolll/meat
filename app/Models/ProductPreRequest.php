<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPreRequest extends Model
{
    const STATUS_NEW = 1;
    const STATUS_USER_CANCELED = 2;
    const STATUS_SUPPLIER_REFUSED = 3;
    const STATUS_HAND_PRODUCT_REQUEST = 4;
    const STATUS_DONE = 5;
    const STATUS_ERROR = 6;

    /**
     * @return array
     */
    public static function statuses()
    {
        return [
            self::STATUS_NEW => ProductRequestSupplierStatus::ID_NEW,
            self::STATUS_USER_CANCELED => ProductRequestSupplierStatus::ID_USER_CANCELED,
            self::STATUS_SUPPLIER_REFUSED => ProductRequestSupplierStatus::ID_SUPPLIER_REFUSED,
            self::STATUS_HAND_PRODUCT_REQUEST => 'hand_product_request',
            self::STATUS_DONE => ProductRequestSupplierStatus::ID_DONE,
            self::STATUS_ERROR => 'error',
        ];
    }

    /**
     * @param $status
     * @return string
     */
    public static function getStatusName($status): string
    {
        return in_array($status, array_flip(self::statuses()), true) ? self::statuses()[$status] : '';
    }

    /**
     * @var array
     */
    protected $fillable = [
        'user_uuid',
        'product_request_uuid',
        'product_uuid',
        'quantity',
        'status',
        'delivery_date',
        'confirmed_delivery_date',
        'error'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'quantity' => 0,
        'status' => self::STATUS_NEW,
    ];

    protected $dates = [
        'delivery_date',
        'confirmed_delivery_date',
    ];

    /**
     * @return BelongsTo
     */
    public function productRequest()
    {
        return $this->belongsTo(ProductRequest::class, 'product_request_uuid', 'uuid');
    }

    /**
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
