<?php

namespace App\Models\ProductRequests;

use App\Models\ProductPreRequest;
use App\Models\ProductRequest;
use App\Models\ProductRequestSupplierStatus;

class SupplierProductRequest extends ProductRequest
{
    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'supplier-product-request';

    /**
     * @var string
     */
    protected $table = 'product_requests';

    const STATUS_SUITABLE_FOR_CONFIRMED_DATE = [
        ProductRequestSupplierStatus::ID_IN_WORK,
        ProductRequestSupplierStatus::ID_MATCHING,
        ProductRequestSupplierStatus::ID_ON_THE_WAY,
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function (SupplierProductRequest $model) {
            $wasDone = $model->getOriginal('product_request_supplier_status_id') === ProductRequestSupplierStatus::ID_DONE;
            $nowDone = $model->product_request_supplier_status_id === ProductRequestSupplierStatus::ID_DONE;

            if (!$wasDone && $nowDone) {
                app('warehouse.transactions.providers.supplier-product-request')->produce($model);
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function relatedCustomerProductRequests()
    {
        return $this->belongsToMany(
            CustomerProductRequest::class,
            'customer_product_request_supplier_product_request',
            'supplier_product_request_uuid',
            'customer_product_request_uuid'
        );
    }

    public function isStatusSuitableForConfirmedDate()
    {
        return in_array($this->product_request_supplier_status_id, self::STATUS_SUITABLE_FOR_CONFIRMED_DATE, true);
    }

    public function productPreRequests()
    {
        return $this->hasMany(ProductPreRequest::class, 'product_request_uuid', 'uuid');
    }
}
