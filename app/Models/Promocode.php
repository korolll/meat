<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Promocode extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @var array
     */
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'discount_percent',
        'min_price',
        'enabled',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'discount_percent' => 'float',
        'min_price' => 'float',
    ];

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
}
