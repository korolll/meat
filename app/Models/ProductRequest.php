<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Observers\ProductRequestObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRequest extends Model
{
    use SoftDeletes;

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
        'product_request_customer_status_id' => ProductRequestCustomerStatus::ID_NEW,
        'product_request_supplier_status_id' => ProductRequestSupplierStatus::ID_NEW,
        'product_request_delivery_status_id' => ProductRequestDeliveryStatus::ID_NEW,
        'price' => 0,
        'weight' => 0,
        'volume' => 0,
    ];

    /**
     * @var array
     */
    protected $dates = [
        'expected_delivery_date',
        'confirmed_date'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_partial_delivery' => 'boolean',
    ];

    protected $fillable = [
        'confirmed_date'
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            ProductRequestObserver::class,
        ]);
    }

    /**
     * @return bool
     */
    public function getIsWaitingForDeliveryAttribute()
    {
        return $this->product_request_delivery_status_id === ProductRequestDeliveryStatus::ID_WAITING;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeWaitingForDelivery(Builder $query)
    {
        return $query->where('product_request_delivery_status_id', ProductRequestDeliveryStatus::ID_WAITING);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customerUser()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplierUser()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deliveryUser()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productRequestCustomerStatus()
    {
        return $this->belongsTo(ProductRequestCustomerStatus::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productRequestSupplierStatus()
    {
        return $this->belongsTo(ProductRequestSupplierStatus::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productRequestDeliveryStatus()
    {
        return $this->belongsTo(ProductRequestDeliveryStatus::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transportation()
    {
        return $this->belongsTo(Transportation::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_product_request', 'product_request_uuid')
            ->withPivot(['quantity', 'quantity_actual', 'price', 'weight', 'volume', 'is_added_product']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function partialDeliveryProducts()
    {
        return $this->belongsToMany(Product::class, 'product_product_request', 'product_request_uuid')
            ->wherePivot('quantity', '!=', \DB::raw('quantity_actual'));
    }

    /**
     * @param Carbon $confirmedDate
     * @return ProductRequest
     */
    public function setConfirmedDate($confirmedDate)
    {
        if ($confirmedDate){
            $this->confirmed_date = $confirmedDate;
        }

        return $this;
    }
}
