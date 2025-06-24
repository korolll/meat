<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Services\Management\Client\Product\Discount\DiscountModelInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PromoDiverseFoodClientDiscount extends Model implements DiscountModelInterface
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
        'discount_percent',
        'start_at',
        'end_at'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'discount_percent' => 'float',
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'start_at',
        'end_at'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getActiveFrom(): CarbonInterface
    {
        return $this->start_at;
    }

    /**
     * @return \Carbon\CarbonInterface
     */
    public function getActiveTo(): CarbonInterface
    {
        return $this->end_at;
    }
}
