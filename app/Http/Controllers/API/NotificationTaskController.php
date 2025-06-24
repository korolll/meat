<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationTaskStoreRequest;
use App\Http\Resources\NotificationTaskResource;
use App\Http\Responses\NotificationTaskCollectionResponse;
use App\Models\NotificationTask;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class NotificationTaskController extends Controller
{
    /**
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', NotificationTask::class);
        return NotificationTaskCollectionResponse::create(NotificationTask::query());
    }

    /**
     * @param \App\Models\NotificationTask $notificationTask
     *
     * @return \App\Http\Resources\NotificationTaskResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(NotificationTask $notificationTask)
    {
        $this->authorize('view', $notificationTask);

        return NotificationTaskResource::make($notificationTask);
    }

    /**
     * @param \App\Http\Requests\NotificationTaskStoreRequest $request
     *
     * @return \App\Http\Resources\NotificationTaskResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(NotificationTaskStoreRequest $request)
    {
        $this->authorize('create', NotificationTask::class);

        $validated = $request->validated();
        $notificationTask = new NotificationTask($request->validated());
        $notificationTask->save();

        $clientUuids = Arr::get($validated, 'client_uuids');
        if ($clientUuids) {
            $notificationTask->clients()->sync($clientUuids);
        }

        return NotificationTaskResource::make($notificationTask);
    }

    /**
     * @param \App\Http\Requests\NotificationTaskStoreRequest $request
     * @param \App\Models\NotificationTask                    $notificationTask
     *
     * @return \App\Http\Resources\NotificationTaskResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(NotificationTaskStoreRequest $request, NotificationTask $notificationTask)
    {
        $this->authorize('update', $notificationTask);
        if ($notificationTask->executed_at) {
            throw new BadRequestHttpException('You can\'t change executed task');
        }

        $validated = $request->validated();
        $notificationTask->fill($request->validated());
        $notificationTask->save();

        $clientUuids = Arr::get($validated, 'client_uuids', []);
        $notificationTask->clients()->sync($clientUuids);

        return NotificationTaskResource::make($notificationTask);
    }

    /**
     * @param \App\Models\NotificationTask $notificationTask
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(NotificationTask $notificationTask)
    {
        $this->authorize('delete', $notificationTask);
        if ($notificationTask->executed_at) {
            throw new BadRequestHttpException('You can\'t change executed task');
        }

        $notificationTask->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }
}
