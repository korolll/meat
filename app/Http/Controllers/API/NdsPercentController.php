<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class NdsPercentController extends Controller
{
    /**
     * @return mixed
     */
    public function index()
    {
        return [
            'data' => config('app.nds-percents'),
        ];
    }
}
