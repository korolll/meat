<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\API\CreatePushTokenRequest;
use App\Models\Client;
use App\Models\ClientPushToken;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PushTokenController extends Controller
{
    /**
     * @param \App\Http\Requests\API\Clients\CreatePushTokenRequest $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(CreatePushTokenRequest $request)
    {
        $validated = $request->validated();
        DB::transaction(function () use ($validated) {
            Client::query()
                ->where('uuid', $this->client->uuid)
                ->lockForUpdate()
                ->first();

            ClientPushToken::query()
                ->where('client_uuid', $this->client->uuid)
                ->where('id', '!=', $validated['token'])
                ->delete();

            $pushToken = ClientPushToken::findOrNew($validated['token']);
            $pushToken->id = $validated['token'];
            $pushToken->client()->associate($this->client);
            $pushToken->updateTimestamps();
            $pushToken->save();
        });

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Models\ClientPushToken $pushToken
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(ClientPushToken $pushToken)
    {
        $this->authorize('destroy', [$pushToken]);
        $pushToken->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
