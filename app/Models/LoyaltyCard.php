<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoyaltyCard extends Model
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
        'discount_percent' => 0,
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'loyalty_card_type_uuid',
        'number',
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
     * @param Builder $query
     * @param string $loyaltyCardTypeUuid
     * @param string $number
     * @return Builder
     */
    public function scopeHasTypeNumber(Builder $query, $loyaltyCardTypeUuid, $number)
    {
        return $query->where([
            'loyalty_cards.loyalty_card_type_uuid' => $loyaltyCardTypeUuid,
            'loyalty_cards.number' => $number,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loyaltyCardType()
    {
        return $this->belongsTo(LoyaltyCardType::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }
}
