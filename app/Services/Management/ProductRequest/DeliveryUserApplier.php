<?php

namespace App\Services\Management\ProductRequest;

use App\Exceptions\ClientExceptions\ProductRequestAlreadyHasDeliveryUserException;
use App\Models\ProductRequest;
use App\Models\ProductRequestDeliveryStatus;
use App\Models\User;

class DeliveryUserApplier implements DeliveryUserApplierContract
{
    /**
     * @var ProductRequest
     */
    protected $productRequest;

    /**
     * @param ProductRequest $productRequest
     */
    public function __construct(ProductRequest $productRequest)
    {
        $this->productRequest = $productRequest;
    }

    /**
     * @param User $user
     * @param null|string $comment
     * @return ProductRequest
     * @throws \App\Exceptions\TealsyException
     */
    public function apply(User $user, ?string $comment = null)
    {
        if ($this->productRequest->delivery_user_uuid !== null) {
            throw new ProductRequestAlreadyHasDeliveryUserException();
        }

        $this->productRequest->deliveryUser()->associate($user);

        return $this->setDeliveryStatusId(ProductRequestDeliveryStatus::ID_IN_WORK, $comment);
    }

    /**
     * @param null|string $comment
     * @return ProductRequest
     * @throws \App\Exceptions\TealsyException
     */
    public function reset(?string $comment = null)
    {
        if ($this->productRequest->delivery_user_uuid === null) {
            return $this->productRequest;
        }

        $this->productRequest->deliveryUser()->dissociate();

        return $this->setDeliveryStatusId(ProductRequestDeliveryStatus::ID_WAITING, $comment);
    }

    /**
     * @param string $deliveryStatusId
     * @param null|string $comment
     * @return ProductRequest
     * @throws \App\Exceptions\TealsyException
     */
    protected function setDeliveryStatusId(string $deliveryStatusId, ?string $comment)
    {
        $manager = app(StatusTransitionManagerContract::class, [
            'productRequest' => $this->productRequest,
        ]);

        return $manager->transition('product_request_delivery_status_id', $deliveryStatusId, [
            'delivery_comment' => $comment
        ]);
    }
}
