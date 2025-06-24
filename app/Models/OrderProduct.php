<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Services\Management\Client\Product\PriceData;
use App\Services\Management\Client\Product\PriceDataInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

class OrderProduct extends Model
{
    const MORPH_TYPE_ALIAS = 'order_product';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var array
     */
    protected $fillable = [
        'order_uuid',
        'product_uuid',
        'quantity',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'quantity' => 'double',
        'total_weight' => 'double',

        'discount' => 'double',
        'price_with_discount' => 'double',

        'total_discount' => 'double',
        'total_amount_with_discount' => 'double',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(GenerateUuidPrimary::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the owning commentable model.
     */
    public function discountable()
    {
        return $this->morphTo('discountable', 'discountable_type', 'discountable_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function rating()
    {
        return $this->morphOne(RatingScore::class, 'rated_through_reference');
    }

    /**
     * @return \App\Services\Management\Client\Product\PriceData
     */
    public function getPriceData(): PriceData
    {
        return new PriceData([
            'price_with_discount' => $this->price_with_discount,
            'discount' => $this->discount,
            'total_discount' => $this->total_discount,
            'total_amount_with_discount' => $this->total_amount_with_discount,
            'total_weight' => $this->total_weight,
            'total_quantity' => $this->quantity,
            'total_bonus' => $this->total_bonus,
            'paid_bonus' => $this->paid_bonus,
            'discount_model' => $this->discountable
        ]);
    }

    /**
     * @param \App\Services\Management\Client\Product\PriceDataInterface $data
     *
     * @return $this
     */
    public function applyPriceData(PriceDataInterface $data): self
    {
        $this->discount = $data->getDiscount();
        $this->price_with_discount = $data->getPriceWithDiscount();

        $this->total_discount = $data->getTotalDiscount();
        $this->total_amount_with_discount = $data->getTotalAmountWithDiscount();

        $this->total_weight = $data->getTotalWeight();
        $this->quantity = $data->getTotalQuantity();
        $this->total_bonus = $data->getTotalBonus();
        $this->paid_bonus = $data->getPaidBonus();
        $this->discountable()->associate($data->getDiscountModel());

        return $this;
    }
}
