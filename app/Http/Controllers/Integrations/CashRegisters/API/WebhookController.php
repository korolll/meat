<?php

namespace App\Http\Controllers\Integrations\CashRegisters\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\CashRegisters\API\UpdateStopListRequest;
use App\Jobs\UpdateProductStopListJob;

class WebhookController extends Controller
{
    /**
     * @param \App\Http\Requests\Integrations\CashRegisters\API\UpdateStopListRequest $request
     *
     * @return void
     */
    public function updateStopList(UpdateStopListRequest $request)
    {
        $orgId = $request->get('organizationId');
        UpdateProductStopListJob::dispatch([$orgId]);
    }
}
