<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use App\Services\Management\Client\Product\Discount\DiscountModelInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClientActivePromoFavoriteAssortment extends Model implements DiscountModelInterface
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
        'client_uuid',
        'assortment_uuid',
        'discount_percent',
        'active_from',
        'active_to',
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'active_from',
        'active_to',
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
        $format = $this->getDateFormat();
        return $query->whereBetweenColumns(DB::raw("'" . $moment->format($format) . "'"), ['active_from', 'active_to']);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\CarbonInterface|null          $moment
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreateAtDay(Builder $query, ?CarbonInterface $moment = null): Builder
    {
        $moment = $moment ?: now();
        return $query->whereBetween('created_at', [
            $moment->startOfDay(),
            $moment->endOfDay(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
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
        return $this->active_from;
    }

    public function getActiveTo(): CarbonInterface
    {
        return $this->active_to;
    }
}
