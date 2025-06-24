<?php

namespace App\Events;

use App\Models\Driver;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverRegistered
{
    use Dispatchable, SerializesModels;

    /**
     * @var Driver
     */
    public $driver;

    /**
     * @var string
     */
    public $password;

    /**
     * DriverRegistered constructor.
     *
     * @param Driver $driver
     * @param string $password
     */
    public function __construct(Driver $driver, $password)
    {
        $this->driver = $driver;
        $this->password = $password;
    }
}
