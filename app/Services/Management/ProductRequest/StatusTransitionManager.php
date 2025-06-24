<?php

namespace App\Services\Management\ProductRequest;

use App\Exceptions\ClientExceptions\StatusTransitionImpossibleException;
use App\Models\ProductRequest;
use Illuminate\Support\Arr;

class StatusTransitionManager implements StatusTransitionManagerContract
{
    /**
     * @var ProductRequest
     */
    protected $productRequest;

    /**
     * @var array
     */
    protected $transitionVariants;

    /**
     * @param ProductRequest $productRequest
     * @param array $transitionVariants
     */
    public function __construct(ProductRequest $productRequest, array $transitionVariants)
    {
        $this->productRequest = $productRequest;
        $this->transitionVariants = $transitionVariants;
    }

    /**
     * @param string $statusAttribute
     * @param string $nextStatusId
     * @param array $additionalAttributes
     * @return ProductRequest
     * @throws \App\Exceptions\TealsyException
     */
    public function transition($statusAttribute, $nextStatusId, $additionalAttributes = [])
    {
        $attributes = $this->getTransitionAttributes($statusAttribute, $nextStatusId);

        foreach ($attributes as $key => $value) {
            $this->productRequest->setAttribute($key, $value);
        }

        foreach ($additionalAttributes as $key => $value) {
            $this->productRequest->setAttribute($key, $value);
        }

        return $this->productRequest;
    }

    /**
     * @param string $statusAttribute
     * @param string $nextStatusId
     * @return array
     * @throws \App\Exceptions\TealsyException
     */
    protected function getTransitionAttributes($statusAttribute, $nextStatusId)
    {
        $attributes = Arr::get($this->transitionVariants, implode('.', [
            $statusAttribute,
            $this->productRequest->$statusAttribute,
            $nextStatusId,
        ]));

        if ($attributes === null) {
            throw new StatusTransitionImpossibleException();
        }

        return $attributes;
    }
}
