<?php

namespace App\Services\Management\ProductRequest;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface ProductRequestWrapperContract
{
    /**
     * @param User $customerUser
     * @return static
     */
    public function setCustomerUser(User $customerUser);

    /**
     * @param User $supplierUser
     * @return static
     */
    public function setSupplierUser(User $supplierUser);

    /**
     * @param Product $product
     * @param int $quantity
     * @return static
     * @throws \App\Exceptions\TealsyException
     */
    public function attachProduct(Product $product, $quantity);

    /**
     * @param CarbonInterface $expectedDeliveryDate
     * @return static
     */
    public function setExpectedDeliveryDate(CarbonInterface $expectedDeliveryDate);

    /**
     * @param string $deliveryMethodId
     * @return static
     */
    public function setDeliveryMethodId(string $deliveryMethodId);

    /**
     * @param Collection $supplierProductRequestUuids
     * @return static
     */
    public function setSupplierProductsRequestUuids(Collection $supplierProductRequestUuids);

    /**
     * @throws \Throwable
     * @return ProductRequest
     */
    public function saveOrFail();
}
