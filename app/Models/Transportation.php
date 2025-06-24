<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transportation extends Model
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
    protected $attributes = [
        'transportation_status_id' => TransportationStatus::ID_NEW,
    ];

    /**
     * @var array
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'started_at',
        'finished_at',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'date',
        'car_uuid',
        'driver_uuid',
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
     * @return bool
     */
    public function getIsDoneAttribute()
    {
        return $this->transportation_status_id === TransportationStatus::ID_DONE;
    }

    /**
     * @return bool
     */
    public function getIsOnTheWayAttribute()
    {
        return $this->transportation_status_id === TransportationStatus::ID_ON_THE_WAY;
    }

    /**
     * @param \Carbon\CarbonInterface|null $startedAt
     * @return $this
     */
    public function start($startedAt = null)
    {
        $this->transportation_status_id = TransportationStatus::ID_ON_THE_WAY;
        $this->started_at = $startedAt ?: now();

        return $this;
    }

    /**
     * @return $this
     */
    public function finish()
    {
        $this->transportation_status_id = TransportationStatus::ID_DONE;
        $this->finished_at = now();

        return $this;
    }

    /**
     * @return $this
     */
    public function tryFinish()
    {
        if ($this->finished_at !== null) {
            return $this;
        }

        if ($this->transportationPoints()->notVisited()->exists()) {
            return $this;
        }

        return $this->finish();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function car()
    {
        return $this->belongsTo(Car::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transportationStatus()
    {
        return $this->belongsTo(TransportationStatus::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|TransportationPoint
     */
    public function transportationPoints()
    {
        return $this->hasMany(TransportationPoint::class);
    }
}
