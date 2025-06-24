<?php

namespace App\Services\Storaging\Warehouse;

use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ReceiptLine;
use Illuminate\Support\Arr;

class OrderWarehouseTransactionProducer extends AbstractWarehouseTransactionProducer
{
    /**
     * @var \App\Models\Order
     */
    protected $model;

    /**
     * @var \Illuminate\Support\Collection|ReceiptLine[]
     */
    protected $lines;

    /**
     * @var \App\Models\OrderProduct|null
     */
    protected ?OrderProduct $changedProduct = null;

    /**
     * @var float
     */
    protected float $changedProductDelta = 0;

    /**
     * @var int
     */
    protected int $modifier = 0;

    /**
     * @var array<string, int>
     */
    protected array $productToDeltaMap = [];

    /**
     * @param \App\Models\OrderProduct $changedProduct
     * @param float                    $delta
     *
     * @return $this
     */
    public function setChangedProduct(OrderProduct $changedProduct, float $delta): self
    {
        $this->changedProductDelta = $delta;
        $this->changedProduct = $changedProduct;

        return $this;
    }

    /**
     * @param int $modifier
     *
     * @return $this
     */
    public function setOrderLineModifier(int $modifier): self
    {
        $this->modifier = $modifier;

        return $this;
    }

    /**
     * @return array<Product>
     */
    public function getProducts()
    {
        if ($this->changedProduct) {
            return [$this->changedProduct->product];
        }

        if (! $this->model->relationLoaded('orderProducts')) {
            $orderProducts = $this->model->orderProducts()->with('product')->get();
            $this->model->setRelation('orderProducts', $orderProducts);
        } else {
            $orderProducts = $this->model->orderProducts;
        }

        $products = [];
        $this->productToDeltaMap = [];
        foreach ($orderProducts as $orderProduct) {
            $products[] = $orderProduct->product;
            $quantity = $orderProduct->quantity;
            if ($this->modifier) {
                $quantity *= $this->modifier;
            }

            $this->productToDeltaMap[$orderProduct->product_uuid] = $quantity;
        }

        return $products;
    }

    /**
     * @param \App\Models\Product $product
     *
     * @return float
     */
    public function getProductQuantityDelta(Product $product)
    {
        if ($this->changedProduct) {
            return $this->changedProductDelta;
        }

        return (float)Arr::get($this->productToDeltaMap, $product->uuid, 0);
    }
}
