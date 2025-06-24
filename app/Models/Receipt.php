<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Services\Management\Client\Product\CollectionPriceDataInterface;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'receipt';

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
    protected $dates = [
        'created_at',
        'received_at',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe(GenerateUuidPrimary::class);

        static::creating(function (Receipt $model) {
            $model->received_at = now();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loyaltyCardType()
    {
        return $this->belongsTo(LoyaltyCardType::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receiptLines()
    {
        return $this->hasMany(ReceiptLine::class);
    }

    /**
     * @param \App\Services\Management\Client\Product\CollectionPriceDataInterface $data
     *
     * @return $this
     */
    public function applyCollectionPriceData(CollectionPriceDataInterface $data): self
    {
        $this->total_bonus = $data->getTotalBonus();
        $this->paid_bonus = $data->getPaidBonus();
        $this->total = $data->getTotalPriceWithDiscount();

        return $this;
    }
}
