<?php

namespace App\Models;

use App\Observers\GenerateCreatedAt;
use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransaction extends Model
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
    protected $casts = [
        'quantity_old' => 'float',
        'quantity_delta' => 'float',
        'quantity_new' => 'float',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            GenerateCreatedAt::class,
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeTimeOrdered(Builder $query)
    {
        return $query->orderBy($this->primaryKey);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function reference()
    {
        return $this->morphTo();
    }
}
