<?php

namespace App\Http\Controllers\API\Profile\ProductRequests;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerProductRequestImportRequest;
use App\Imports\CustomerProductRequestImport;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;

class ProductRequestImportController extends Controller
{
    /**
     * @param CustomerProductRequestImportRequest $request
     * @param User $supplierUser
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function import(CustomerProductRequestImportRequest $request, User $supplierUser)
    {
        $this->authorize('import-product-requests', User::class);
        /**
         * @var $file UploadedFile
         */
        $file = $request->file('file');
        $filePath = $file->path();
        $importedProductRequestUuids = Collection::make();
        (new CustomerProductRequestImport($supplierUser, $importedProductRequestUuids, true))->import($filePath, null, Excel::XLSX);

        return response([
            'new_product_request_uuids' => $importedProductRequestUuids->toArray()
        ], Response::HTTP_CREATED);
    }
}
