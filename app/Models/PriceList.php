<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Observers\PriceList\PriceListObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceList extends Model
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
        'price_list_status_id' => PriceListStatus::FUTURE,
    ];

    /**
     * @var array
     */
    protected $dates = [
        'date_from',
        'date_till',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'date_from',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            PriceListObserver::class,
        ]);
    }

    /**
     * @return bool
     */
    public function getIsFutureAttribute()
    {
        return $this->price_list_status_id === PriceListStatus::FUTURE;
    }

    /**
     * @return bool
     */
    public function getIsCurrentAttribute()
    {
        return $this->price_list_status_id === PriceListStatus::CURRENT;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeArchive(Builder $query)
    {
        return $query->where('price_list_status_id', PriceListStatus::ARCHIVE);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeCurrent(Builder $query)
    {
        return $query->where('price_list_status_id', PriceListStatus::CURRENT);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeFuture(Builder $query)
    {
        return $query->where('price_list_status_id', PriceListStatus::FUTURE);
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
    public function customerUser()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priceListStatus()
    {
        return $this->belongsTo(PriceListStatus::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot(['price_old', 'price_new']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activeProducts()
    {
        return $this->products()->where('is_active', true);
    }
}
