<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoDiverseFoodClientStat extends Model
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
    protected $attributes = [
        'purchased_count' => 0,
        'rated_count' => 0
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'client_uuid',
        'month',
        'purchased_count',
        'rated_count'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'discount_percent' => 'float',
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
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function promoDiverseFoodClientStatAssortments()
    {
        return $this->hasMany(PromoDiverseFoodClientStatAssortment::class);
    }
}
