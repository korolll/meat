<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoDiverseFoodSettings extends Model
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
    protected $fillable = [
        'count_purchases',
        'count_rating_scores',
        'discount_percent',
        'is_enabled',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'discount_percent' => 'float',
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

        static::observe([GenerateUuidPrimary::class]);
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
