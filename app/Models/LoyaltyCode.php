<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyCode extends Model
{
    use HasFactory;

    protected $table = 'loyalty_codes';

    public $timestamps = false;

    protected $fillable = [
        'client_uuid', 'code', 'expires_on'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'expires_on',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_uuid', 'uuid');
    }
}
