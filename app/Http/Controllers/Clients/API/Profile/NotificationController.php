<?php

namespace App\Http\Controllers\Clients\API\Profile;

use App\Http\Controllers\Controller;
use App\Http\Responses\NotificationsCollectionResponse;
use App\Services\Framework\Http\CollectionRequest;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;


class NotificationController extends Controller
{
    /**
     * @param \App\Services\Framework\Http\CollectionRequest $request
     *
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     */
    public function index(CollectionRequest $request)
    {
        return NotificationsCollectionResponse::create($this->user->notifications()->WhereNull('deleted_at'));
    }

    /**
     * @param \Illuminate\Notifications\DatabaseNotification $notification
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function read(DatabaseNotification $notification)
    {
        $this->authorize('read', $notification);
        $notification->markAsRead();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function readAll()
    {
        $this->client->notifications()
            ->unread()
            ->update([
                'read_at' => now()
            ]);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function deleteAll()
    {
        $this->client->notifications()
            ->update([
                'deleted_at' => now()
            ]);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \Illuminate\Notifications\DatabaseNotification $notification
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(DatabaseNotification $notification)
    {
        $this->authorize('delete', $notification);
        $notification->update([
                'deleted_at' => now()
            ]);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
