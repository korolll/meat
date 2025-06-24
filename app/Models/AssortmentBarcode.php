<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssortmentBarcode extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assortment_barcodes';

    const DELETED_AT = 'finished_at';

    /**
     * @var array
     */
    protected $fillable = [
        'assortment_uuid',
        'barcode',
        'is_active',
    ];

    protected $dates = [
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function assortment()
    {
        return $this->belongsTo(Assortment::class, 'assortment_uuid', 'uuid');
    }

    public function setCreatedAt($value)
    {
        parent::setCreatedAt($value);

        $this->started_at = $value;
    }
}
