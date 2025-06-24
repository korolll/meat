<?php

namespace App\Models;

use App\Observers\GenerateCreatedAt;
use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Model;

class ClientAuthenticationToken extends Model
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
