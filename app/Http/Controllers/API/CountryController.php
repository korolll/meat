<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\CountryCollectionResponse;
use App\Models\Country;

class CountryController extends Controller
{
    /**
     * @return mixed
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Country::class);

        return CountryCollectionResponse::create(
            Country::query()
        );
    }
}
