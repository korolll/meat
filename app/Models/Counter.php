<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $attributes = [
        'value' => 0,
        'step' => 1
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'id'
    ];
}
