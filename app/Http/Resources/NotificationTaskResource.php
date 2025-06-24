<?php

namespace App\Http\Resources;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;

class NotificationTaskResource extends JsonResource
{
    /**
     * @param mixed $resource
     */
    public static function loadMissing($resource)
    {
        $resource->loadMissing([
            'clients' => function (Relation $query) {
                return $query->select('uuid');
            },
        ]);
    }

    /**
     * @param \App\Models\NotificationTask $notificationTask
     *
     * @return array
     */
    public function resource($notificationTask)
    {
        return [
            'uuid' => $notificationTask->uuid,
            'title_template' => $notificationTask->title_template,
            'body_template' => $notificationTask->body_template,
            'options' => $notificationTask->options,

            'execute_at' => $notificationTask->execute_at,
            'taken_to_work_at' => $notificationTask->taken_to_work_at,
            'executed_at' => $notificationTask->executed_at,

            'created_at' => $notificationTask->created_at,
            'updated_at' => $notificationTask->updated_at,

            'client_uuids' => $notificationTask->clients->pluck('uuid')
        ];
    }
}
