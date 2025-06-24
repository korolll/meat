<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

/**
 * @property-read Authenticatable $actor
 * @property-read Client $client
 * @property-read Driver $driver
 * @property-read User $user
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @return void
     */
    public function unavailable()
    {
        abort(Response::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'actor':
            case 'client':
            case 'driver':
            case 'user':
                return auth()->user();
        }

        throw new \InvalidArgumentException("Undefined property {$name}");
    }
}
