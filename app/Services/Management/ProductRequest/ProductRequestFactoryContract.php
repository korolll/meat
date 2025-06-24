<?php

namespace App\Services\Management\ProductRequest;

use Illuminate\Support\Collection;

interface ProductRequestFactoryContract
{
    /**
     * @param Collection $supplierProductRequestUuids
     * @return static
     */
    public function setSupplierProductsRequestUuids(Collection $supplierProductRequestUuids);

    /**
     * @param Collection $productQuantity
     * @return static
     */
    public function setProductQuantity(Collection $productQuantity);

    /**
     * @param Collection $productExpectedDeliveryDate
     * @return $this|ProductRequestFactoryContract
     */
    public function setExpectedDeliveryDate(Collection $productExpectedDeliveryDate);

    /**
     * @param string $deliveryMethodId
     * @return $this|ProductRequestFactoryContract
     */
    public function setDeliveryMethodId(string $deliveryMethodId);

    /**
     * @param bool $isSendErrorsArray
     * @return ProductRequestWrapperContract[]|Collection
     */
    public function make($isSendErrorsArray);

    /**
     * @param string $customerUserUuid
     * @return static
     */
    public function setCustomerUserUuid(string $customerUserUuid);
}
