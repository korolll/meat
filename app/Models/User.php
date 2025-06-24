<?php

namespace App\Models;

use App\Models\LaboratoryTests\CustomerLaboratoryTest;
use App\Models\LaboratoryTests\ExecutorLaboratoryTest;
use App\Models\ProductRequests\CustomerProductRequest;
use App\Models\ProductRequests\DeliveryProductRequest;
use App\Models\ProductRequests\SupplierProductRequest;
use App\Notifications\API\UserResetPassword;
use App\Observers\GenerateUuidPrimary;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\RoutesNotifications;

class User extends Authenticatable
{
    use SoftDeletes, RoutesNotifications;

    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'user';

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
        'user_verify_status_id' => UserVerifyStatus::ID_NEW,
        'is_email_verified' => false,
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_email_verified' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'user_type_id',
        'full_name',
        'legal_form_id',
        'organization_name',
        'organization_address',
        'address',
        'email',
        'phone',
        'password',
        'inn',
        'kpp',
        'ogrn',
        'region_uuid',
        'position',
        'bank_correspondent_account',
        'bank_current_account',
        'bank_identification_code',
        'bank_name',
        'address_latitude',
        'address_longitude',
        'work_hours_from',
        'work_hours_till',
        'brand_name',

        'signer_type_id',
        'signer_full_name',
        'power_of_attorney_number',
        'date_of_power_of_attorney',
        'ip_registration_certificate_number',
        'date_of_ip_registration_certificate',

        'has_parking',
        'has_ready_meals',
        'has_atms',
        'image_uuid',
        'allow_find_nearby',

        'user_delivery_zone_id',
    ];

    protected $dates = [
        'date_of_ip_registration_certificate',
        'date_of_power_of_attorney',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            UserObserver::class
        ]);
    }

    /**
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'email';
    }

    /**
     * @return null|string
     */
    public function getRememberTokenName()
    {
        return null;
    }

    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(UserResetPassword::make($this, $token));
    }

    /**
     * @return string
     */
    public function routeNotificationForSms()
    {
        return $this->phone;
    }

    /**
     * @return array
     */
    public function routeNotificationForMail()
    {
        return array_merge([$this->email], $this->userAdditionalEmails()->pluck('email')->all());
    }

    /**
     * @return string
     */
    public function getEmailVerifyTokenAttribute()
    {
        return encrypt($this->email);
    }

    /**
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        return $this->user_type_id === UserType::ID_ADMIN;
    }

    /**
     * @return bool
     */
    public function getIsDeliveryServiceAttribute()
    {
        return $this->user_type_id === UserType::ID_DELIVERY_SERVICE;
    }

    /**
     * @return bool
     */
    public function getIsDistributionCenterAttribute()
    {
        return $this->user_type_id === UserType::ID_DISTRIBUTION_CENTER;
    }

    /**
     * @return bool
     */
    public function getIsStoreAttribute()
    {
        return $this->user_type_id === UserType::ID_STORE;
    }

    /**
     * @return bool
     */
    public function getIsSupplierAttribute()
    {
        return $this->user_type_id === UserType::ID_SUPPLIER;
    }

    /**
     * @return bool
     */
    public function getIsLaboratoryAttribute()
    {
        return $this->user_type_id === UserType::ID_LABORATORY;
    }

    /**
     * @return bool
     */
    public function getIsVerifiedAttribute()
    {
        return $this->user_verify_status_id === UserVerifyStatus::ID_APPROVED;
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    public function getWorkHoursFromAttribute($value)
    {
        if (is_string($value) && strlen($value) > 5) {
            return substr($value, 0, 5);
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    public function getWorkHoursTillAttribute($value)
    {
        if (is_string($value) && strlen($value) > 5) {
            return substr($value, 0, 5);
        }

        return $value;
    }

    /**
     * @param string|null $value
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * @param Builder $query
     * @param string $token
     * @return Builder
     */
    public function scopeHasEmailVerificationToken(Builder $query, $token)
    {
        return $query->where('email', decrypt($token));
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeStore(Builder $query)
    {
        return $query->where('user_type_id', UserType::ID_STORE);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeAdmin(Builder $query)
    {
        return $query->where('user_type_id', UserType::ID_ADMIN);
    }

    /**
     * Те, кто могут предоставить продукцию для заказа в системе
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeProductSellers(Builder $query)
    {
        return $query->whereIn('user_type_id', [UserType::ID_SUPPLIER, UserType::ID_DISTRIBUTION_CENTER]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userType()
    {
        return $this->belongsTo(UserType::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legalForm()
    {
        return $this->belongsTo(LegalForm::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userVerifyStatus()
    {
        return $this->belongsTo(UserVerifyStatus::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function catalogs()
    {
        return $this->hasMany(Catalog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|PriceList
     */
    public function priceLists()
    {
        return $this->hasMany(PriceList::class);
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
    public function assortmentMatrix()
    {
        return $this->belongsToMany(Assortment::class, 'assortment_matrices');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customerProductRequests()
    {
        return $this->hasMany(CustomerProductRequest::class, 'customer_user_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function supplierProductRequests()
    {
        return $this->hasMany(SupplierProductRequest::class, 'supplier_user_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deliveryProductRequests()
    {
        return $this->hasMany(DeliveryProductRequest::class, 'delivery_user_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transportations()
    {
        return $this->hasMany(Transportation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stocktakings()
    {
        return $this->hasMany(Stocktaking::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function loyaltyCardTypes()
    {
        return $this->belongsToMany(LoyaltyCardType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function customerRating()
    {
        return $this->rating()->hasType(RatingType::ID_CUSTOMER);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function customerRatingScores()
    {
        return $this->ratingScores()->hasRatedThroughType(SupplierProductRequest::MORPH_TYPE_ALIAS);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function supplierRating()
    {
        return $this->rating()->hasType(RatingType::ID_SUPPLIER);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function supplierRatingScores()
    {
        return $this->ratingScores()->hasRatedThroughType(CustomerProductRequest::MORPH_TYPE_ALIAS);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne|Rating
     */
    protected function rating()
    {
        return $this->morphOne(Rating::class, 'reference');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|RatingScore
     */
    protected function ratingScores()
    {
        return $this->morphMany(RatingScore::class, 'rated_reference');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userAdditionalEmails()
    {
        return $this->hasMany(UserAdditionalEmail::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(File::class)
            ->where('files.file_category_id', FileCategory::ID_USER_FILE)
            ->withPivot('public_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function catalogCachedProductCounts(): BelongsToMany
    {
        return $this->belongsToMany(Catalog::class, 'user_catalog_product_counts')
            ->withPivot('product_count');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customerLaboratoryTests()
    {
        return $this->hasMany(CustomerLaboratoryTest::class, 'customer_user_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function executorLaboratoryTests()
    {
        return $this->hasMany(ExecutorLaboratoryTest::class, 'executor_user_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function signerType()
    {
        return $this->belongsTo(SignerType::class)->withTrashed();
    }

    /**
     * @param $email
     */
    public function setEmail($email)
    {
        $this->email = mb_strtolower($email);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function image()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'store_user_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function paymentVendorSettings()
    {
        return $this->belongsToMany(PaymentVendorSetting::class)->withPivot('is_active');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function paymentVendorSettingsIsActive()
    {
        return $this->paymentVendorSettings()
            ->wherePivot('is_active', '=', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deliveryZone()
    {
        return $this->belongsTo(UserDeliveryZone::class, 'user_delivery_zone_id', 'id');
    }
}
