<?php

namespace App\Models;

use App\Observers\GenerateCreatedAt;
use App\Observers\GenerateUuidPrimary;
use App\Observers\StocktakingObserver;
use Illuminate\Database\Eloquent\Model;

class Stocktaking extends Model
{
    /**
     * Алиас для полиморфных связей
     */
    const MORPH_TYPE_ALIAS = 'stocktaking';

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
        'approved_at',
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
            StocktakingObserver::class,
        ]);
    }

    /**
     * @return bool
     */
    public function getIsApprovedAttribute()
    {
        return $this->approved_at !== null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['write_off_reason_id', 'quantity_old', 'quantity_new', 'comment']);
    }
}
