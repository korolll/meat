<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDeliveryZone extends Model
{
    use HasFactory;

    /**
     * Таблица, связанная с моделью.
     *
     * @var string
     */
    protected $table = 'user_delivery_zones';

    /**
     * Атрибуты, которые можно массово заполнять.
     *
     * @var array
     */
    protected $fillable = [
        'less_zone_price',
        'between_zone_price',
        'more_zone_price',
        'less_zone_distance',
        'between_zone_distance',
        'more_zone_distance',
        'max_zone_distance',
    ];

    /**
     * Связь с моделью User (один ко многим).
     */
    public function users()
    {
        return $this->hasMany(User::class, 'user_delivery_zone_id', 'id');
    }
}
