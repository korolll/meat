<?php

namespace App\Models;

use App\Observers\AssortmentObserver;
use App\Observers\GenerateUuidPrimary;
use App\Services\Database\Table\DiscountForbiddenCatalogRecursiveTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;


class Assortment extends Model
{
    use SoftDeletes, Searchable;

    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'assortment';

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
    protected $casts = [
        'is_storable' => 'boolean',
        'declaration_end_date' => 'date',
    ];

//    /**
//     * @var array
//     */
//    protected $dates = [
//        'declaration_end_date'
//    ];

    /**
     * @var array
     */
    protected $attributes = [
        'assortment_verify_status_id' => AssortmentVerifyStatus::ID_NEW,
        'short_name' => '',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'catalog_uuid',
        'name',
        'assortment_unit_id',
        'country_id',
        'okpo_code',
        'weight',
        'volume',
        'manufacturer',
        'ingredients',
        'description',
        'group_barcode',
        'temperature_min',
        'temperature_max',
        'production_standard_id',
        'production_standard_number',
        'is_storable',
        'shelf_life',
        'nds_percent',
        'short_name',
        'assortment_brand_uuid',
        'declaration_end_date',
        'article',
        'bonus_percent',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            AssortmentObserver::class,
        ]);
    }

    /**
     * @return bool
     */
    public function getIsApprovedAttribute()
    {
        return $this->assortment_verify_status_id === AssortmentVerifyStatus::ID_APPROVED;
    }

    /**
     * @return bool
     */
    public function getIsDeclinedAttribute()
    {
        return $this->assortment_verify_status_id === AssortmentVerifyStatus::ID_DECLINED;
    }

    /**
     * @return bool
     */
    public function isForbiddenForDiscount(): bool
    {
        $bannedCatalogsTable = new DiscountForbiddenCatalogRecursiveTable();
        $exist = $bannedCatalogsTable->table('banned_catalogs')
            ->where('banned_catalogs.catalog_uuid', $this->catalog_uuid)
            ->exists();

        if ($exist) {
            return true;
        }

        return DiscountForbiddenAssortment::whereAssortmentUuid($this->uuid)->exists();
    }

    /**
     * @param iterable<static> $assortments
     *
     * @return array
     */
    public static function isForbiddenForDiscountList(iterable $assortments): array
    {
        $catalogUuids = [];
        $assortmentUuids = [];
        foreach ($assortments as $assortment) {
            $catalogUuids[$assortment->catalog_uuid] = $assortment->catalog_uuid;
            $assortmentUuids[] = $assortment->uuid;
        }

        $bannedCatalogsTable = new DiscountForbiddenCatalogRecursiveTable();
        $bannedCatalogs = $bannedCatalogsTable->table('banned_catalogs')
            ->select('banned_catalogs.catalog_uuid')
            ->whereIn('banned_catalogs.catalog_uuid', $catalogUuids)
            ->get()
            ->keyBy('catalog_uuid');

        $bannedAssortments = DiscountForbiddenAssortment::query()
            ->whereIn('assortment_uuid', $assortmentUuids)
            ->get('assortment_uuid')
            ->keyBy('assortment_uuid');

        $result = [];
        foreach ($assortments as $assortment) {
            $uuid = $assortment->uuid;
            if ($bannedCatalogs->has($assortment->catalog_uuid) || $bannedAssortments->has($uuid)) {
                $result[$uuid] = true;
            } else {
                $result[$uuid] = false;
            }
        }

        return $result;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeApproved(Builder $query)
    {
        return $query->where('assortment_verify_status_id', AssortmentVerifyStatus::ID_APPROVED);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function declarationExpired(Builder $query)
    {
        return $query->where('declaration_date_end', '<', now());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function catalog()
    {
        return $this->belongsTo(Catalog::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assortmentUnit()
    {
        return $this->belongsTo(AssortmentUnit::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assortmentVerifyStatus()
    {
        return $this->belongsTo(AssortmentVerifyStatus::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function images()
    {
        return $this->belongsToMany(File::class)
            ->wherePivot('file_category_id', FileCategory::ID_ASSORTMENT_IMAGE)
            ->withPivot('public_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(File::class)
            ->wherePivot('file_category_id', FileCategory::ID_ASSORTMENT_FILE)
            ->withPivot('public_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return array
     */
    public function toSearchableArray()
    {
        $fields = [
            'name' => $this->name,
            'ingredients' => $this->ingredients,
        ];

        return array_map('mb_strtolower', $fields);
    }

    /**
     * @return array
     */
    public function searchableOptions()
    {
        return [
            'rank' => [
                'fields' => [
                    'name' => 'A',
                    'ingredients' => 'B',
                ],
            ],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function ratingScores()
    {
        return $this->morphMany(RatingScore::class, 'rated_reference');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function rating()
    {
        return $this->morphOne(Rating::class, 'reference');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|User
     */
    public function assortmentMatrixUsers()
    {
        return $this->belongsToMany(User::class, 'assortment_matrices');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|User
     */
    public function stores()
    {
        // @todo Исправь меня на скоуп, он есть, но висит в PR'e
        return $this->assortmentMatrixUsers()->where('user_type_id', UserType::ID_STORE);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|User
     */
    public function favoriteAssortmentClients()
    {
        return $this->belongsToMany(Client::class, 'assortment_client_favorites');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assortmentProperties()
    {
        return $this->belongsToMany(AssortmentProperty::class)->withPivot('value');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assortmentMarkedProperties()
    {
        return $this->belongsToMany(AssortmentProperty::class)
            ->wherePivot('value', '=', 'Маркировка')
            ->withPivot('value');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clientShoppingLists()
    {
        return $this->belongsToMany(ClientShoppingList::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assortmentBrand()
    {
        return $this->belongsTo(AssortmentBrand::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function barcodes()
    {
        return $this->hasMany(AssortmentBarcode::class);
    }

    public function mealReceipts()
    {
        return $this->belongsToMany(MealReceipt::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany | \App\Models\PromotionInTheShopLastPurchase[]
     */
    public function promotionInTheShopLastPurchases(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, (new PromotionInTheShopLastPurchase())->getTable());
    }
}
