<?php

namespace App\Models;

use App\Observers\ClientPromoFavoriteAssortmentVariantObserver;
use App\Observers\GenerateUuidPrimary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPromoFavoriteAssortmentVariant extends Model
{
    use HasFactory;

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
        'client_uuid',
        'can_be_activated_till',
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'can_be_activated_till'
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::observe([
            GenerateUuidPrimary::class,
            ClientPromoFavoriteAssortmentVariantObserver::class
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
