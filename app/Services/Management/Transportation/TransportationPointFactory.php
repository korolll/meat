<?php

namespace App\Services\Management\Transportation;

use App\Models\ProductRequest;
use App\Models\Transportation;
use App\Models\TransportationPoint;
use App\Models\TransportationPointType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransportationPointFactory implements TransportationPointFactoryContract
{
    /**
     * @var Transportation
     */
    protected $transportation;

    /**
     * @var Collection|ProductRequest[]
     */
    protected $productRequests = [];

    /**
     * @var int
     */
    protected $nextPointOrder = 0;

    /**
     * @param Transportation $transportation
     */
    public function __construct(Transportation $transportation)
    {
        $this->transportation = $transportation;
    }

    /**
     * @param Collection|ProductRequest[] $productRequests
     * @return $this
     */
    public function setProductRequests(Collection $productRequests)
    {
        $this->productRequests = $productRequests->unique();

        return $this;
    }

    /**
     * @return Collection|TransportationPoint[]
     * @throws \Throwable
     */
    public function create()
    {
        return DB::transaction(function () {
            $this->transportation->save();

            foreach ($this->productRequests as $productRequest) {
                $this->transportation->transportationPoints()->saveMany(
                    $this->makePoints($productRequest)
                );

                $productRequest->transportation()->associate($this->transportation);
                $productRequest->save();
            }

            return $this->transportation->transportationPoints;
        });
    }

    /**
     * @param ProductRequest $productRequest
     * @return array
     */
    protected function makePoints(ProductRequest $productRequest)
    {
        return [
            $this->makeLoadingPoint($productRequest),
            $this->makeUnloadingPoint($productRequest),
        ];
    }

    /**
     * @param ProductRequest $productRequest
     * @return TransportationPoint
     */
    protected function makeLoadingPoint(ProductRequest $productRequest)
    {
        $address = $productRequest->supplierUser->address;

        return $this->makePoint($productRequest, $address, TransportationPointType::ID_LOADING);
    }

    /**
     * @param ProductRequest $productRequest
     * @return TransportationPoint
     */
    protected function makeUnloadingPoint(ProductRequest $productRequest)
    {
        $address = $productRequest->customerUser->address;

        return $this->makePoint($productRequest, $address, TransportationPointType::ID_UNLOADING);
    }

    /**
     * @param ProductRequest $productRequest
     * @param string $address
     * @param string $transportationPointTypeId
     * @return TransportationPoint
     */
    protected function makePoint(ProductRequest $productRequest, $address, $transportationPointTypeId)
    {
        $point = new TransportationPoint();
        $point->productRequest()->associate($productRequest);
        $point->transportation_point_type_id = $transportationPointTypeId;
        $point->address = $address;
        $point->order = $this->getNextPointOrder();

        return $point;
    }

    /**
     * @return int
     */
    protected function getNextPointOrder()
    {
        return $this->nextPointOrder++;
    }
}
