<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNotificationSetting extends Model
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
    protected $keyType = 'string';

    // Real PK: ['order_status_id, 'order_delivery_type_id']
    protected $primaryKey = 'order_status_id';

    protected $casts = [
        'notification_sms' => 'array',
        'notification_mail' => 'array',
        'notification_push' => 'array',
        'notification_database' => 'array',
    ];

    protected $fillable = [
        'order_status_id',
        'order_delivery_type_id',
        'notification_sms',
        'notification_mail',
        'notification_push',
        'notification_database',
    ];
}
