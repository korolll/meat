<?php

namespace App\Services\Management\Product;

use App\Exceptions\ServerException;
use App\Models\Catalog;
use App\Models\Product;
use App\Models\User;
use App\Services\Management\Product\Contracts\ProductReplicatorContract;

class ProductReplicator implements ProductReplicatorContract
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * @var User
     */
    protected $recipient;

    /**
     * @var Catalog
     */
    protected $catalog;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @param Product $product
     * @param User $recipient
     * @param Catalog $catalog
     * @param array $attributes
     * @return Product
     * @throws \App\Exceptions\TealsyException
     */
    public function replicate(Product $product, User $recipient, Catalog $catalog, array $attributes = [])
    {
        if (!$recipient->exists) {
            throw new ServerException('Recipient model is not exists in database');
        }

        if ($catalog->user_uuid !== $recipient->uuid) {
            throw new ServerException('Recipient user does not own specified catalog');
        }

        if ($product->user_uuid === $recipient->uuid) {
            return $product;
        }

        $this->product = $product;
        $this->recipient = $recipient;
        $this->catalog = $catalog;
        $this->attributes = $attributes;

        if (($product = $this->maybeProductAlreadyExists()) !== null) {
            return $product;
        }

        return $this->replicateProduct();
    }

    /**
     * @return Product|null
     */
    protected function maybeProductAlreadyExists()
    {
        return $this->recipient->products()
            ->where('assortment_uuid', $this->product->assortment_uuid)
            ->first();
    }

    /**
     * @return Product
     */
    protected function replicateProduct()
    {
        $product = $this->product->replicate(['category_uuid', 'user_uuid']);

        $product->forceFill($this->attributes);
        $product->catalog()->associate($this->catalog);
        $product->user()->associate($this->recipient);
        $product->save();

        return $product;
    }
}
