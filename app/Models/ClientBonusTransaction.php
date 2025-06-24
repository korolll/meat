<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientBonusTransaction extends Model
{
    use HasFactory;

    const REASON_MANUAL = 'manual';

    const REASON_PURCHASE_DONE = 'purchase_done';

    const REASON_DONE_PURCHASE_CANCELLED = 'done_purchase_cancelled';

    const REASON_PAID_PURCHASE_CANCELLED = 'paid_purchase_cancelled';

    const REASON_PAID_PURCHASE_CHANGED = 'paid_purchase_changed';

    const REASON_PURCHASE_PAID = 'purchase_paid';

    const REASON_PROFILE_FILLED = 'profile_filled';

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
    protected $fillable = [];

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
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function relatedReference()
    {
        return $this->morphTo();
    }
}
