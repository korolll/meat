<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Services\Management\Client\Product\Discount\DiscountModelInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 *
 */
class ClientPromotion extends Model implements DiscountModelInterface
{
    use HasFactory;

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
        'user_uuid',
        'promotion_type',
        'discount_percent',
        'started_at',
        'expired_at',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'discount_percent' => 'float'
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'started_at',
        'expired_at',
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\CarbonInterface|null          $moment
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveAt(Builder $query, ?CarbonInterface $moment = null): Builder
    {
        $moment = $moment ?: now();
        return $query
            ->whereRaw('?::timestamptz <@ tstzrange(started_at, expired_at)')
            ->addBinding($moment);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany | \App\Models\PromotionInTheShopAssortment[]
     */
    public function promotionInTheShopAssortments(): HasMany
    {
        return $this->hasMany(PromotionInTheShopAssortment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getActiveFrom(): CarbonInterface
    {
        return $this->started_at;
    }

    public function getActiveTo(): CarbonInterface
    {
        return $this->expired_at;
    }
}
