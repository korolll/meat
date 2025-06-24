<?php

namespace App\Models;

use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\RoutesNotifications;

class Driver extends Authenticatable
{
    use SoftDeletes, RoutesNotifications;

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
        'full_name',
        'email',
        'password',
        'hired_on',
        'fired_on',
        'comment',
        'license_number',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
        ]);

        static::creating(function (Driver $driver) {
            $driver->hired_on = now();
        });
    }

    /**
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'email';
    }

    /**
     * @return null|string
     */
    public function getRememberTokenName()
    {
        return null;
    }

    /**
     * @param string|null $value
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transportations()
    {
        return $this->hasMany(Transportation::class);
    }
}
