<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppContact extends Model
{
    protected $table = 'app_contact';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email',
        'call_center_number',
        'social_network_instagram',
        'social_network_vk',
        'social_network_facebook',
        'social_messenger_telegram',
        'delivey_information',
        'ios_version',
        'android_version'
    ];

    public $timestamps = false;
}
