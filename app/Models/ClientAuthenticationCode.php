<?php

namespace App\Models;

use App\Observers\GenerateCreatedAt;
use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Model;

class ClientAuthenticationCode extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var bool
     */
    public $timestamps = false;

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
        'code' => 'string',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'code',
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([GenerateUuidPrimary::class, GenerateCreatedAt::class]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }
}
