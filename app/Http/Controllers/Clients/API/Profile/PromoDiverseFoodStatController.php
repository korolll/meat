<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Clients\API\Profile\PromoDiverseFoodClientStatResource;
use App\Http\Responses\Clients\API\PromoDiverseFoodClientStatResponse;
use App\Models\PromoDiverseFoodClientStat;

class PromoDiverseFoodStatController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index-owned', PromoDiverseFoodClientStat::class);
        $client = $this->client;

        $lastYearColl = $client->promoDiverseFoodClientStats();
        return PromoDiverseFoodClientStatResponse::create($lastYearColl);
    }

    /**
     * @param \App\Models\PromoDiverseFoodClientStat $stat
     *
     * @return \App\Http\Resources\Clients\API\Profile\PromoDiverseFoodClientStatResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(PromoDiverseFoodClientStat $stat)
    {
        $this->authorize('view', $stat);
        return PromoDiverseFoodClientStatResource::make($stat);
    }
}
