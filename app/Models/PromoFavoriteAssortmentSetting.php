<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Observers\PromoFavoriteAssortmentSettingObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoFavoriteAssortmentSetting extends Model
{
    use SoftDeletes, HasFactory;

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
        'threshold_amount',
        'discount_percent',
        'number_of_sum_days',
        'number_of_active_days',
        'is_enabled',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'threshold_amount' => 'double',
        'discount_percent' => 'double',
        'number_of_sum_days' => 'integer',
        'number_of_active_days' => 'integer',
        'is_enabled' => 'bool'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_enabled' => true
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            PromoFavoriteAssortmentSettingObserver::class,
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeEnabled(Builder $query)
    {
        return $query->where('is_enabled', '=', true);
    }
}
