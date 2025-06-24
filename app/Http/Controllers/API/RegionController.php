<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\RegionCollectionResponse;
use App\Models\Region;

class RegionController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     */
    public function index()
    {
        return RegionCollectionResponse::create(
            Region::query()
        );
    }
}
