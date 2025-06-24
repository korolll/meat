<?php

namespace App\Observers;

use App\Events\CustomerProductRequestStatusChanged;
use App\Events\SupplierProductRequestStatusChanged;
use App\Models\ProductRequest;
use App\Models\ProductRequestDeliveryMethod;
use App\Models\ProductRequestDeliveryStatus;
use App\Services\Management\ProductRequest\ProductRequestSelfDeliveryProviderContract;

class ProductRequestObserver
{
    /**
     * Handle the product request "updated" event.
     *
     * @param ProductRequest $productRequest
     * @return void
     */
    public function updated(ProductRequest $productRequest)
    {
        if ($productRequest->wasChanged('product_request_customer_status_id')) {
            CustomerProductRequestStatusChanged::dispatch($productRequest);
        }
        if ($productRequest->wasChanged('product_request_supplier_status_id')) {
            SupplierProductRequestStatusChanged::dispatch($productRequest);
        }
    }

    /**
     * Handle the product request "updating" event.
     *
     * @param ProductRequest $productRequest
     * @return void
     */
    public function updating(ProductRequest $productRequest)
    {
        $wasWaiting = $productRequest->getOriginal('product_request_delivery_status_id') === ProductRequestDeliveryStatus::ID_WAITING;
        $nowWaiting = $productRequest->product_request_delivery_status_id === ProductRequestDeliveryStatus::ID_WAITING;

        if (!$wasWaiting && $nowWaiting
            && $productRequest->product_request_delivery_method_id === ProductRequestDeliveryMethod::ID_SELF_DELIVERY) {
            app(ProductRequestSelfDeliveryProviderContract::class)->provide($productRequest);
        }
    }
}
