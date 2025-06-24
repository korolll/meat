<?php

namespace App\Services\Management\ProductRequest;

use App\Events\ProductRequestCreated;
use App\Exceptions\ClientExceptions\QuantityOfProductMustBeAtLeastException;
use App\Exceptions\ClientExceptions\QuantityOfProductMustBeMultipleException;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\ProductRequests\CustomerProductRequest;
use App\Models\User;
use App\Services\Traits\CollectErrors;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Product request creator
 *
 * Class ProductRequestWrapper
 * @package App\Services\Management\ProductRequest
 */
class ProductRequestWrapper implements ProductRequestWrapperContract
{
    use CollectErrors;

    /**
     * @var CustomerProductRequest
     */
    protected $productRequest;
    /**
     * @var User
     */
    protected $customerUser;
    /**
     * @var User
     */
    protected $supplierUser;
    /**
     * @var array
     */
    protected $products;
    /**
     * @var array
     */
    protected $productsForAttach;
    /**
     * @var
     */
    protected $expectedDeliveryDate;
    /**
     * @var string
     */
    protected $deliveryMethodId;
    /**
     * @var array
     */
    protected $supplierProductRequestUuids;

    /**
     * @return CustomerProductRequest
     */
    public function getProductRequest()
    {
        return $this->productRequest;
    }

    /**
     * @param User $customerUser
     * @return static
     */
    public function setCustomerUser(User $customerUser)
    {
        $this->customerUser = $customerUser;

        return $this;
    }

    /**
     * @param User $supplierUser
     * @return static
     */
    public function setSupplierUser(User $supplierUser)
    {
        $this->supplierUser = $supplierUser;

        return $this;
    }

    /**
     * @param string $deliveryMethodId
     * @return $this|ProductRequestWrapperContract
     */
    public function setDeliveryMethodId(string $deliveryMethodId)
    {
        $this->deliveryMethodId = $deliveryMethodId;

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
     * @param Product $product
     * @param int $quantity
     * @return static
     * @throws \App\Exceptions\TealsyException
     * @throws \Exception
     */
    public function attachProduct(Product $product, $quantity)
    {
        if ($quantity < $product->min_quantity_in_order) {
            $this->setOrThrowException(new QuantityOfProductMustBeAtLeastException($product, $product->min_quantity_in_order));
        }

        if ($quantity % $product->quantum > 0) {
            $this->setOrThrowException(new QuantityOfProductMustBeMultipleException($product, $product->quantum));
        }

        if ($this->supplierUser === null) {
            $this->supplierUser = $product->user;
        }

        $this->products[$product->uuid] = $product;
        $this->productsForAttach[$product->uuid] = [
            'quantity' => $quantity,
            'quantity_actual' => $quantity,
            'price' => $product->price
        ];

        return $this;
    }

    /**
     * @param CarbonInterface $expectedDeliveryDate
     * @return $this|ProductRequestWrapperContract
     * @throws \App\Exceptions\TealsyException
     */
    public function setExpectedDeliveryDate(CarbonInterface $expectedDeliveryDate)
    {
        $dateValidator = app(ProductRequestExpectedDeliveryDateValidator::class);
        if ($this->getIsSendErrorsArray()) {
            $dateValidator->setIsSendErrorsArray(true);
        }
        if ($dateValidator->validate($expectedDeliveryDate, $this->products)) {
            $this->expectedDeliveryDate = $expectedDeliveryDate;
        } else {
            $this->errors = array_merge($this->errors, $dateValidator->getErrors());
        }

        return $this;
    }

    /**
     * @return ProductRequest|false
     * @throws \Throwable
     */
    public function saveOrFail()
    {
        if ($this->errors) {
            return false;
        }

        return DB::transaction(function () {
            $this->createProductRequest();
            $this->attachProducts();
            $this->attachRelatedSupplierProductRequests();

            // При создании заявки проверять uuid исполнителя и если есть совпадение с uuid в настройках, то не выгружать заявку в 1С.
            $userExistsInBlacklist = $this->userExistsInBlacklist();
            ProductRequestCreated::dispatch($this->productRequest, !$userExistsInBlacklist, true);

            return $this->productRequest;
        });
    }

    protected function userExistsInBlacklist(): bool
    {
        $users = config('services.1c.users_allowed_to_export_only_after_confirmed_date', []);

        return in_array($this->productRequest->supplier_user_uuid, $users, true);
    }

    /**
     * @return static
     * @throws \Throwable
     */
    protected function createProductRequest()
    {
        $this->productRequest = new CustomerProductRequest();
        $this->productRequest->customer_user_uuid = $this->customerUser->uuid;
        $this->productRequest->supplier_user_uuid = $this->supplierUser->uuid;
        $this->productRequest->expected_delivery_date = $this->expectedDeliveryDate;
        $this->productRequest->product_request_delivery_method_id = $this->deliveryMethodId;
        $this->productRequest->save();

        return $this;
    }

    /**
     * @return static
     */
    protected function attachProducts()
    {
        $this->productRequest->products()->attach($this->productsForAttach);

        return $this;
    }

    /**
     * @return static
     */
    protected function attachRelatedSupplierProductRequests()
    {
        $this->productRequest->relatedSupplierProductRequests()->sync($this->supplierProductRequestUuids);

        return $this;
    }

    /**
     * @return User
     */
    public function getCustomerUser(): User
    {
        return $this->customerUser;
    }

    /**
     * @return User
     */
    public function getSupplierUser(): User
    {
        return $this->supplierUser;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @return array
     */
    public function getProductsForAttach(): array
    {
        return $this->productsForAttach;
    }

    /**
     * @return mixed
     */
    public function getExpectedDeliveryDate()
    {
        return $this->expectedDeliveryDate;
    }

    /**
     * @return string
     */
    public function getDeliveryMethodId(): string
    {
        return $this->deliveryMethodId;
    }

    /**
     * @return array
     */
    public function getSupplierProductRequestUuids(): array
    {
        return $this->supplierProductRequestUuids;
    }
}
