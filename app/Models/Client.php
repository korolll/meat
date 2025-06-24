<?php

namespace App\Models;

use App\Observers\ClientObserver;
use App\Observers\GenerateUuidPrimary;
use App\Services\Framework\Contracts\Auth\TokenAuthenticatable;
use App\Services\Framework\Notifications\NotifiableUuid;
use App\Services\Models\Client\ClientCartInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\RoutesNotifications;

class Client extends Authenticatable implements TokenAuthenticatable
{
    use SoftDeletes, RoutesNotifications, NotifiableUuid;

    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'client';

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
        'phone',
        'name',
        'email',
        'birth_date',
        'is_agree_with_diverse_food_promo',
        'sex',
        'consent_to_service_newsletter',
        'consent_to_receive_promotional_mailings',
        'selected_store_user_uuid',
        'shopping_cart_data',
        'app_version',
        'mark_deleted_at',
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'birth_date'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'shopping_cart_data' => 'array',
        'is_agree_with_diverse_food_promo' => 'boolean',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            ClientObserver::class
        ]);
    }

    /**
     * @return \App\Services\Models\Client\ClientCartInterface
     */
    public function getShoppingCart(): ClientCartInterface
    {
        $cart = app(ClientCartInterface::class);
        return $cart->setClient($this);
    }

    /**
     * @param Builder $query
     * @param string  $code
     *
     * @return Builder
     */
    public function scopeHasAuthenticationCode(Builder $query, $code)
    {
        return $query->whereHas('clientAuthenticationCodes', function (Builder $query) use ($code) {
            $query->where('code', $code);
        });
    }

    /**
     * @param Builder $query
     * @param string  $token
     *
     * @return Builder
     */
    public function scopeHasAuthenticationToken(Builder $query, $token)
    {
        return $query->whereHas('clientAuthenticationTokens', function (Builder $query) use ($token) {
            $query->where('uuid', $token);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientAuthenticationCodes()
    {
        return $this->hasMany(ClientAuthenticationCode::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientAuthenticationTokens()
    {
        return $this->hasMany(ClientAuthenticationToken::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function loyaltyCards()
    {
        return $this->hasMany(LoyaltyCard::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchasesView()
    {
        return $this->hasMany(PurchaseView::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function receipts()
    {
        return $this->hasManyThrough(Receipt::class, LoyaltyCard::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoriteAssortments()
    {
        return $this->belongsToMany(Assortment::class, 'assortment_client_favorites');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shoppingLists()
    {
        return $this->hasMany(ClientShoppingList::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|User
     */
    public function favoriteStores()
    {
        return $this->belongsToMany(User::class, 'client_user_favorites');
    }

        /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|User
     */
    public function favoriteMealReceipts()
    {
        return $this->belongsToMany(MealReceipt::class, 'client_meal_receipt_favorites');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|User
     */
    public function selectedStore()
    {
        return $this->hasOne(User::class, 'uuid', 'selected_store_user_uuid');
    }

    /**
     * Specifies the client's FCM tokens
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        return $this->clientPushTokens->pluck('id')->all();
    }

    /**
     * @return string|null
     */
    public function routeNotificationForMail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function routeNotificationForSms()
    {
        return $this->phone;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientPushTokens(): HasMany
    {
        return $this->hasMany(ClientPushToken::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientDeliveryAddresses()
    {
        return $this->hasMany(ClientDeliveryAddress::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientCreditCards(): HasMany
    {
        return $this->hasMany(ClientCreditCard::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientPromoFavoriteAssortmentVariants(): HasMany
    {
        return $this->hasMany(ClientPromoFavoriteAssortmentVariant::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function promoDiverseFoodClientStats(): HasMany
    {
        return $this->hasMany(PromoDiverseFoodClientStat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function promoDiverseFoodClientDiscounts(): HasMany
    {
        return $this->hasMany(PromoDiverseFoodClientDiscount::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientActivePromoFavoriteAssortments(): HasMany
    {
        return $this->hasMany(ClientActivePromoFavoriteAssortment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\ClientPromotion[]
     */
    public function clientPromotions(): HasMany
    {
        return $this->hasMany(ClientPromotion::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\ClientPromotion[]
     */
    public function clientBonusTransactions(): HasMany
    {
        return $this->hasMany(ClientBonusTransaction::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany | \App\Models\PromotionInTheShopLastPurchase[]
     */
    public function promotionInTheShopLastPurchases(): BelongsToMany
    {
        return $this->belongsToMany(Assortment::class, 'promotion_in_the_shop_last_purchases');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|RatingScore
     */
    public function ratingScoresBy()
    {
        return $this->morphMany(RatingScore::class, 'rated_by_reference');
    }

    public function isDefaultClient(): bool
    {
        return $this->phone === config('auth.default_client_phone');
    }
}
