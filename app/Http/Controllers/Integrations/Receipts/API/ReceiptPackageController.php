<?php

namespace App\Http\Controllers\Integrations\Receipts\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReceiptPackageStoreRequest;
use App\Jobs\CreateReceipt;

class ReceiptPackageController extends Controller
{
    /**
     * @param ReceiptPackageStoreRequest $request
     * @return mixed
     */
    public function store(ReceiptPackageStoreRequest $request)
    {
        $rawReceipts = $request->getRawReceipts();

        foreach ($rawReceipts as $attributes) {
            CreateReceipt::dispatch($attributes);
        }

        return ['processed' => count($rawReceipts)];
    }
}
