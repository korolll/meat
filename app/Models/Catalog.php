<?php

namespace App\Models;

use App\Observers\CalculateCatalogLevel;
use App\Observers\Catalog\CatalogObserver;
use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Catalog extends Model
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
        'assortments_count' => 0,
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'catalog_uuid',
        'image_uuid',
        'name',
        'sort_number',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            CalculateCatalogLevel::class,
            CatalogObserver::class,
        ]);
    }

    /**
     * @return bool
     */
    public function getIsPublicAttribute()
    {
        return $this->user_uuid === null;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePublic(Builder $query)
    {
        return $query->whereNull($this->qualifyColumn('user_uuid'));
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
    public function parent()
    {
        return $this->belongsTo(Catalog::class, 'catalog_uuid')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function child()
    {
        return $this->hasMany(Catalog::class, 'catalog_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assortments()
    {
        return $this->hasMany(Assortment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function assortmentsThroughProducts()
    {
        return $this->hasManyThrough(Assortment::class, Product::class, null, 'uuid', null, 'assortment_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assortmentProperties()
    {
        return $this->belongsToMany(AssortmentProperty::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function image()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userCachedProductCounts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_catalog_product_counts')
            ->withPivot([
                'product_count',
                'properties',
                'tags',
            ]);
    }
}
