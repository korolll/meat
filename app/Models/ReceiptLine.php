<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Services\Management\Client\Product\PriceDataInterface;
use Illuminate\Database\Eloquent\Model;

class ReceiptLine extends Model
{
    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'receipt-line';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = false;

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
    protected $casts = [
        'quantity' => 'float',
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
    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assortment()
    {
        return $this->belongsTo(Assortment::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function rating()
    {
        return $this->morphOne(RatingScore::class, 'rated_through_reference');
    }

    /**
     * Get the owning commentable model.
     */
    public function discountable()
    {
        return $this->morphTo('discountable', 'discountable_type', 'discountable_uuid');
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

        $this->quantity = $data->getTotalQuantity();
        $this->total_bonus = $data->getTotalBonus();
        $this->paid_bonus = $data->getPaidBonus();
        $this->discountable()->associate($data->getDiscountModel());

        return $this;
    }
}
