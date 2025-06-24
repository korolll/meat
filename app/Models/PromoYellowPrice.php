<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Services\Management\Client\Product\Discount\DiscountModelInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class PromoYellowPrice extends Model implements DiscountModelInterface
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
        'assortment_uuid',
        'price',
        'start_at',
        'end_at',
        'is_enabled'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'start_at',
        'end_at'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'price' => 'float',
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
     * Scope a query to only include popular users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
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
        $format = $this->getDateFormat();
        return $query->whereBetweenColumns(DB::raw("'" . $moment->format($format) . "'"), ['start_at', 'end_at']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\BelongsToMany|mixed
     */
    public function stores()
    {
        return $this->belongsToMany(User::class)->scopes(['store']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assortment()
    {
        return $this->belongsTo(Assortment::class);
    }

    public function getActiveFrom(): CarbonInterface
    {
        return $this->start_at;
    }

    public function getActiveTo(): CarbonInterface
    {
        return $this->end_at;
    }
}
