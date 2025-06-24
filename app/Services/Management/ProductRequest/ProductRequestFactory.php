<?php

namespace App\Services\Management\ProductRequest;

use App\Exceptions\TealsyException;
use App\Models\Product;
use App\Services\Models\Product\MakeProductsAvailableForRequestQuery;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Class ProductRequestFactory
 *
 * @package App\Services\Management\ProductRequest
 */
class ProductRequestFactory implements ProductRequestFactoryContract
{
    /**
     * @var Collection
     */
    protected $supplierProductRequestUuids;
    /**
     * @var Collection
     */
    protected $productQuantity;
    /**
     * @var Collection
     */
    protected $productExpectedDeliveryDate;
    /**
     * @var string
     */
    protected $deliveryMethodId;
    /**
     * @var string
     */
    protected $customerUserUuid;

    /**
     * @param string $customerUserUuid
     * @return static
     */
    public function setCustomerUserUuid(string $customerUserUuid)
    {
        $this->customerUserUuid = $customerUserUuid;

        return $this;
    }

    /**
     * @param Collection $supplierProductRequestUuids
     * @return static
     */
    public function setSupplierProductsRequestUuids(Collection $supplierProductRequestUuids)
    {
        $this->supplierProductRequestUuids = $supplierProductRequestUuids;

        return $this;
    }

    /**
     * @param Collection $productQuantity
     * @return static
     */
    public function setProductQuantity(Collection $productQuantity)
    {
        $this->productQuantity = $productQuantity;

        return $this;
    }

    /**
     * @param Collection $productExpectedDeliveryDate
     * @return $this|ProductRequestFactoryContract
     */
    public function setExpectedDeliveryDate(Collection $productExpectedDeliveryDate)
    {
        $this->productExpectedDeliveryDate = $productExpectedDeliveryDate;

        return $this;
    }

    /**
     * @param string $deliveryMethodId
     * @return $this|ProductRequestFactoryContract
     */
    public function setDeliveryMethodId(string $deliveryMethodId)
    {
        $this->deliveryMethodId = $deliveryMethodId;

        return $this;
    }

    /**
     * @param bool $isSendErrorsArray
     * @return ProductRequestWrapper[]|Collection
     */
    public function make($isSendErrorsArray = false)
    {
        return $this->getProductsBySupplier()->map(function (Collection $products) use ($isSendErrorsArray) {
            $expectedDeliveryDate = $this->getProductExpectedDeliveryDate($products->first());

            return $this->makeProductRequest($expectedDeliveryDate, $products, $isSendErrorsArray);
        });
    }

    /**
     * @return Product[]|Collection
     */
    protected function getProductsBySupplier()
    {
        if ($this->customerUserUuid) {
            $userUuid = $this->customerUserUuid;
        } else {
            $userUuid = user() ? user()->uuid : null;
        }

        $products = resolve(MakeProductsAvailableForRequestQuery::class)->make([
            // fixme нужно инжектить вместо хелпера
            'customer_user_uuid' => $userUuid
        ])
            ->whereIn('uuid', $this->productQuantity->keys())
            ->get();

        return $products->mapToGroups(function ($product) {
            $groupId = [
                $product->user_uuid,
                $this->getProductExpectedDeliveryDate($product),
            ];

            return [implode('/', $groupId) => $product];
        });
    }

    /**
     * @param Product $product
     * @return int
     */
    protected function getProductQuantity(Product $product): int
    {
        return $this->productQuantity->get($product->uuid, 0);
    }

    /**
     * @param Product $product
     * @return CarbonInterface
     */
    protected function getProductExpectedDeliveryDate(Product $product): CarbonInterface
    {
        return $this->productExpectedDeliveryDate->get($product->uuid);
    }

    /**
     * @param CarbonInterface $expectedDeliveryDate
     * @param Product[]|Collection $products
     * @param bool $isSendErrorsArray
     * @return ProductRequestWrapper
     * @throws TealsyException
     */
    protected function makeProductRequest(CarbonInterface $expectedDeliveryDate, Collection $products, $isSendErrorsArray = false)
    {
        $request = app(ProductRequestWrapper::class);
        $request->setIsSendErrorsArray($isSendErrorsArray);

        foreach ($products as $product) {
            $request->attachProduct($product, $this->getProductQuantity($product));
        }

        $request->setExpectedDeliveryDate($expectedDeliveryDate);
        $request->setDeliveryMethodId($this->deliveryMethodId);
        $request->setSupplierProductsRequestUuids($this->supplierProductRequestUuids);

        return $request;
    }
}
