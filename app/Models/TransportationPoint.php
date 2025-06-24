<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransportationPoint extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = false;

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
    protected $dates = [
        'arrived_at',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'arrived_at',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function (Builder $query) {
            return $query->orderBy('order');
        });

        static::observe(GenerateUuidPrimary::class);
    }

    /**
     * @return bool
     */
    public function getIsVisitedAttribute()
    {
        return $this->arrived_at !== null;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeNotVisited(Builder $query)
    {
        return $query->whereNull('arrived_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transportation()
    {
        return $this->belongsTo(Transportation::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productRequest()
    {
        return $this->belongsTo(ProductRequest::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transportationPointType()
    {
        return $this->belongsTo(TransportationPointType::class)->withTrashed();
    }
}
