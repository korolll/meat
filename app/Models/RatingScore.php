<?php

namespace App\Models;

use App\Events\RatingScoreCreated;
use App\Events\RatingScoreSaved;
use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class RatingScore extends Model
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
    protected $casts = [
        'additional_attributes' => 'array',
    ];

    /**
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => RatingScoreCreated::class,
        'saved' => RatingScoreSaved::class,
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'rated_reference_type',
        'rated_reference_id',
        'rated_by_reference_type',
        'rated_by_reference_id',
        'rated_through_reference_type',
        'rated_through_reference_id',
        'value',
        'comment',
        'weight',
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
     * @return string|null
     */
    public function getCommentAttribute()
    {
        return $this->getAdditionalAttribute('comment');
    }

    /**
     * @param string $value
     */
    public function setCommentAttribute($value)
    {
        $this->setAdditionalAttribute('comment', $value);
    }

    /**
     * @return int|null
     */
    public function getWeightAttribute()
    {
        return $this->getAdditionalAttribute('weight');
    }

    /**
     * @param int $value
     */
    public function setWeightAttribute($value)
    {
        $this->setAdditionalAttribute('weight', $value);
    }

    /**
     * @param Builder $query
     * @param string $ratedThroughReferenceType
     * @return Builder
     */
    public function scopeHasRatedThroughType(Builder $query, $ratedThroughReferenceType)
    {
        return $query->where('rated_through_reference_type', $ratedThroughReferenceType);
    }

    /**
     * @param Builder $query
     * @param string $ratedByReferenceType
     * @return Builder
     */
    public function scopeHasRatedByType(Builder $query, $ratedByReferenceType)
    {
        return $query->where('rated_by_reference_type', $ratedByReferenceType);
    }

    /**
     * @param Builder $query
     * @param string $ratedReferenceType
     * @return Builder
     */
    public function scopeHasRatedType(Builder $query, $ratedReferenceType)
    {
        return $query->where('rated_reference_type', $ratedReferenceType);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeForAssortmentsByClients(Builder $query)
    {
        return $query->select('rating_scores.*')
            ->join('assortments', 'rating_scores.rated_reference_id', '=', 'assortments.uuid')
            ->join('clients', 'rating_scores.rated_by_reference_id', '=', 'clients.uuid')
            ->hasRatedType(Assortment::MORPH_TYPE_ALIAS)
            ->hasRatedByType(Client::MORPH_TYPE_ALIAS);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function ratedReference()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function ratedByReference()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function ratedThroughReference()
    {
        return $this->morphTo();
    }
}
