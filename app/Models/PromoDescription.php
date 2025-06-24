<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoDescription extends Model
{
    use SoftDeletes;

    const VIRTUAL_FRONTOL_DISCOUNT_UUID = '48fa0378-34fe-429c-9fef-c313403ab212';

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
        'name',
        'title',
        'description',
        'logo_file_uuid',
        'color',
        'is_hidden',
        'subtitle',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_hidden' => 'boolean'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logoFile()
    {
        return $this->belongsTo(File::class);
    }
}
