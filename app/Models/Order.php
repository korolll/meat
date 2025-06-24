<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Observers\OrderObserver;
use App\Services\Management\Client\Product\CollectionPriceData;
use App\Services\Management\Client\Product\CollectionPriceDataInterface;
use App\Services\Money\MoneyHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use \Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'order';

    const VIRTUAL_ATTR_MAX_BONUS = 'max_bonus';

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
    protected $attributes = [
        'order_status_id' => OrderStatus::ID_NEW,
        'is_paid' => false,
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'store_user_uuid',
        'client_uuid',
        'order_delivery_type_id',
        'order_payment_type_id',
        'client_comment',
        'client_email',
        'client_address_data',
        'client_credit_card_uuid',
        'planned_delivery_datetime_from',
        'planned_delivery_datetime_to',
        'courier_phone',
        'promocode',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'client_address_data' => 'array',

        'delivery_price' => 'double',
        'total_price_for_products_with_discount' => 'double',
        'total_discount_for_products' => 'double',
        'total_price' => 'double',

        'total_weight' => 'double',
        'total_quantity' => 'double',

        'is_paid' => 'boolean',
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'planned_delivery_datetime_from',
        'planned_delivery_datetime_to',
    ];

    /**
     * @var array
     */
    protected array $virtualValues = [];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            OrderObserver::class
        ]);
    }

    /**
     * @return bool
     */
    public function getIsFinalStateAttribute()
    {
        return $this->order_status_id === OrderStatus::ID_DONE || $this->order_status_id === OrderStatus::ID_CANCELLED;
    }

    /**
     * @return bool
     */
    public function getIsUnchangeableStateAttribute()
    {
        return $this->getIsFinalStateAttribute() || $this->order_status_id === OrderStatus::ID_COLLECTED || $this->order_status_id === OrderStatus::ID_DELIVERING;
    }

    /**
     * @return int
     */
    public function getTotalPriceKopekAttribute(): int
    {
        return MoneyHelper::toKopek($this->total_price);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clientCreditCard(): BelongsTo
    {
        return $this->belongsTo(ClientCreditCard::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_user_uuid')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderDeliveryType(): BelongsTo
    {
        return $this->belongsTo(OrderDeliveryType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderPaymentType(): BelongsTo
    {
        return $this->belongsTo(OrderPaymentType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function relatedClientPayments()
    {
        return $this->morphMany(ClientPayment::class, 'related_reference');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function relatedClientBonusTransactions()
    {
        return $this->morphMany(ClientBonusTransaction::class, 'related_reference');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function rating()
    {
        return $this->morphOne(RatingScore::class, 'rated_through_reference');
    }

    /**
     * @param string $attr
     * @param mixed  $value
     *
     * @return $this
     */
    public function setVirtualValue(string $attr, $value): self
    {
        $this->virtualValues[$attr] = $value;
        return $this;
    }

    /**
     * @param string $attr
     *
     * @return mixed|null
     */
    public function getVirtualValue(string $attr)
    {
        return $this->virtualValues[$attr] ?? null;
    }

    /**
     * @return \App\Services\Management\Client\Product\CollectionPriceData
     */
    public function getCollectionPriceData(): CollectionPriceData
    {
        return new CollectionPriceData([
            'total_discount' => $this->total_discount_for_products,
            'total_price_with_discount' => $this->total_price_for_products_with_discount,
            'total_weight' => $this->total_weight,
            'total_quantity' => $this->total_quantity,
            'total_bonus' => $this->total_bonus,
            'paid_bonus' => $this->paid_bonus,
        ]);
    }

    /**
     * @param \App\Services\Management\Client\Product\CollectionPriceDataInterface $data
     *
     * @return $this
     */
    public function applyCollectionPriceData(CollectionPriceDataInterface $data): self
    {
        $this->total_discount_for_products = $data->getTotalDiscount();
        $this->total_price_for_products_with_discount = $data->getTotalPriceWithDiscount();
        $this->total_weight = $data->getTotalWeight();
        $this->total_quantity = $data->getTotalQuantity();
        $this->total_bonus = $data->getTotalBonus();
        $this->paid_bonus = $data->getPaidBonus();

        return $this;
    }
}
