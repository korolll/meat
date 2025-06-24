<?php

namespace App\Models\ProductRequests;

use App\Models\ProductRequest;
use App\Models\ProductRequestCustomerStatus;

class CustomerProductRequest extends ProductRequest
{
    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'customer-product-request';

    /**
     * @var string
     */
    protected $table = 'product_requests';

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function (CustomerProductRequest $model) {
            $wasDone = $model->getOriginal('product_request_customer_status_id') === ProductRequestCustomerStatus::ID_DONE;
            $nowDone = $model->product_request_customer_status_id === ProductRequestCustomerStatus::ID_DONE;

            if (!$wasDone && $nowDone) {
                app('warehouse.transactions.providers.customer-product-request')->produce($model);
            }

            $model->is_partial_delivery = $model->partialDeliveryProducts()->exists();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function relatedSupplierProductRequests()
    {
        return $this->belongsToMany(
            SupplierProductRequest::class,
            'customer_product_request_supplier_product_request',
            'customer_product_request_uuid',
            'supplier_product_request_uuid'
        );
    }
}
