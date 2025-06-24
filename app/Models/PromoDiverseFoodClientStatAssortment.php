<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoDiverseFoodClientStatAssortment extends Model
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
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'promo_diverse_food_client_stat_uuid',
        'assortment_uuid',
        'is_rated',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'is_rated' => 'boolean',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([GenerateUuidPrimary::class]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function promoDiverseFoodClientStat()
    {
        return $this->belongsTo(PromoDiverseFoodClientStat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assortment()
    {
        return $this->belongsTo(Assortment::class);
    }
}
