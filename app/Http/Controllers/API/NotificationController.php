<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNotificationRequest;
use App\Models\Client;
use App\Notifications\API\CustomNotification;
use Illuminate\Support\Arr;

class NotificationController extends Controller
{
    /**
     * @param \App\Http\Requests\CreateNotificationRequest $request
     */
    public function store(CreateNotificationRequest $request)
    {
        $valid = $request->validated();
        $notification = new CustomNotification(
            $valid['title'],
            $valid['body'],
            (array)Arr::get($valid, 'meta', [])
        );

        $clientUuids = $valid['client_uuids'];
        foreach ($clientUuids as $clientUuid) {
            $client = Client::findOrFail($clientUuid);
            $client->notify($notification);
        }
    }
}
