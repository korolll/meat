<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Rating extends Model
{
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
        'rating_type_id' => RatingType::ID_COMMON,
    ];

    /**
     * @var array
     */
    protected $casts = [
        'value' => 'float',
        'additional_attributes' => 'array',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'reference_type',
        'reference_id',
        'rating_type_id',
        'value',
        'additional_attributes',
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
     * @param string $attribute
     * @param mixed $default
     * @return mixed
     */
    public function getAdditionalAttribute($attribute, $default = null)
    {
        return Arr::get($this->additional_attributes, $attribute, $default);
    }

    /**
     * @param string $attribute
     * @param mixed $value
     */
    public function setAdditionalAttribute($attribute, $value)
    {
        $attributes = $this->additional_attributes;

        Arr::set($attributes, $attribute, $value);

        $this->additional_attributes = $attributes;
    }

    /**
     * @param Builder $query
     * @param string $ratingTypeId
     * @return Builder
     */
    public function scopeHasType(Builder $query, $ratingTypeId)
    {
        return $query->where('rating_type_id', $ratingTypeId);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ratingType()
    {
        return $this->belongsTo(RatingType::class);
    }
}
