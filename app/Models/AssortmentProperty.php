<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssortmentProperty extends Model
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
    protected $casts = [
        'available_values' => 'array',
        'is_searchable' => 'boolean'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'assortment_property_data_type_id' => AssortmentPropertyDataType::ID_STRING,
        'is_searchable' => false
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'assortment_property_data_type_id',
        'is_searchable'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assortments()
    {
        return $this->belongsToMany(Assortment::class)->withPivot('value');
    }
    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsSearchable(Builder $query)
    {
        return $query->where('is_searchable', true);
    }
}
