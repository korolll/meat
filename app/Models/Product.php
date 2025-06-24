<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
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
        'quantity' => 0,
        'quantum' => 1,
        'min_quantum_in_order' => 1,
        'min_delivery_time' => 24,
        'delivery_weekdays' => '[0,1,2,3,4,5,6]',
        'volume' => 0,
        'is_active' => true,
    ];

    /**
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'quantity' => 'float',
        'delivery_weekdays' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'assortment_uuid',
        'catalog_uuid',
        'quantum',
        'min_quantum_in_order',
        'min_delivery_time',
        'price_recommended',
        'delivery_weekdays',
        'volume',
    ];

    public $loyaltySystemIndexInCheck = null;

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            ProductObserver::class,
        ]);
    }

    /**
     * @return int
     */
    public function getMinQuantityInOrderAttribute()
    {
        return $this->min_quantum_in_order * $this->quantum;
    }

    /**
     * @return bool
     */
    public function getNeedExportToOneCAttribute(): bool
    {
        return in_array($this->user_uuid, (array) config('services.1c.users_allowed_to_export', []));
    }

    /**
     * @param Builder $query
     * @param User|string $user
     * @return Builder
     */
    public function scopeOwnedBy(Builder $query, $user)
    {
        return $query->where('user_uuid', $user instanceof User ? $user->uuid : $user);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeOwnedByProductSellers(Builder $query)
    {
        $query->whereHas('user', function (Builder $query) {
            /** @var Builder|User $query */
            $query->productSellers();
        });

        return $query;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsActive(Builder $query)
    {
        $query->where('is_active', true);

        return $query;
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
    public function assortment()
    {
        return $this->belongsTo(Assortment::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function catalog()
    {
        return $this->belongsTo(Catalog::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function priceLists()
    {
        return $this->belongsToMany(PriceList::class)->withPivot(['price_old', 'price_new']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(File::class)
            ->where('files.file_category_id', FileCategory::ID_PRODUCT_FILE)
            ->withPivot('public_name');
    }

    /**
     * Нужно дополнять where "->with([
     * 'preRequest' => function ($query) use ($productRequestUuid) {
     * $query->where('product_pre_requests.product_request_uuid', '=', $productRequestUuid);
     * }
     * ]);"
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function preRequest()
    {
        return $this->hasOne(ProductPreRequest::class, 'product_uuid', 'uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|AssortmentBarcode
     */
    public function assortmentBarcode()
    {
        return $this->belongsTo(AssortmentBarcode::class, 'assortment_barcode_id', 'id');
    }
}
