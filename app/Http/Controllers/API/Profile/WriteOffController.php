<?php

namespace App\Http\Controllers\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\WriteOffStoreBatchRequest;
use App\Http\Requests\WriteOffStoreRequest;
use App\Http\Resources\WriteOffResource;
use App\Models\WriteOff;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WriteOffController extends Controller
{
    /**
     * @param WriteOffStoreRequest $request
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(WriteOffStoreRequest $request)
    {
        $this->authorize('create', WriteOff::class);

        $writeOff = new WriteOff($request->validated());
        $writeOff->user()->associate($this->user);
        $writeOff->saveOrFail();

        return WriteOffResource::make($writeOff);
    }

    /**
     * @param \App\Http\Requests\WriteOffStoreBatchRequest $request
     *
     * @return \App\Services\Framework\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function storeBatch(WriteOffStoreBatchRequest $request)
    {
        $this->authorize('create', WriteOff::class);

        $validated = $request->validated();
        $products = $validated['products'];
        unset($validated['products']);
        $baseData = $validated;

        $writeOffs = DB::transaction(function() use ($products, $baseData) {
            $writeOffs = [];
            foreach ($products as $product) {
                $writeOff = new WriteOff(array_merge($product, $baseData));
                $writeOff->user()->associate($this->user);
                $writeOff->saveOrFail();
                $writeOffs[] = $writeOff;
            }

            return $writeOffs;
        });

        return WriteOffResource::collection($writeOffs);
    }
}
