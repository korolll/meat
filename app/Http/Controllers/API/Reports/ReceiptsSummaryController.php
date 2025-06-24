<?php

namespace App\Http\Controllers\API\Reports;

use App\Exports\ReceiptSummaryExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportReceiptsSummaryRequest;
use App\Http\Responses\ReportReceiptsSummaryCollectionResponse;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ReceiptsSummaryController extends Controller
{
    /**
     * @param ReportReceiptsSummaryRequest $request
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(ReportReceiptsSummaryRequest $request)
    {
        $this->authorize('report-receipts-summary-index', Receipt::class);

        return ReportReceiptsSummaryCollectionResponse::create($this->getQuery($request));
    }

    /**
     * @param ReportReceiptsSummaryRequest $request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function export(ReportReceiptsSummaryRequest $request)
    {
        $this->authorize('report-receipts-summary-index', Receipt::class);

        return (new ReceiptSummaryExport($this->getQuery($request)->get()))->download('receipts.xlsx');
    }

    /**
     * @param ReportReceiptsSummaryRequest $request
     * @return mixed
     */
    protected function getQuery(ReportReceiptsSummaryRequest $request)
    {
        $dateStart = Date::parse($request->date_start);
        $dateEnd = Date::parse($request->date_end);
        $groupBy = $request->group_by;

        $userUuid = $this->getTargetUserUuid($request);
        $query = Receipt::select([
                // Дата
                DB::raw("GREATEST(date_trunc('{$groupBy}', receipts.created_at), '{$dateStart}') AS date"),
                // Количество
                DB::raw('COUNT(receipts.uuid) as quantity'),
                // Продажи
                DB::raw('SUM(receipts.total) as total'),
            ])
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->groupBy(DB::raw("date_trunc('{$groupBy}', receipts.created_at)"));

        if ($userUuid) {
            $query->where('user_uuid', $userUuid);
        }

        if ($request->loyalty_card_is_applied !== null) {
            if ($request->loyalty_card_is_applied) {
                $query->whereNotNull('receipts.loyalty_card_uuid');
            } else {
                $query->whereNull('receipts.loyalty_card_uuid');
            }
        }

        return Receipt::query()->fromSub($query->toBase(), 'receipts');
    }

    /**
     * @param \App\Http\Requests\ReportReceiptsSummaryRequest $request
     *
     * @return \App\Models\User
     */
    protected function getTargetUserUuid(ReportReceiptsSummaryRequest $request): ?string
    {
        $user = $this->user;
        if (! $user->is_admin) {
            return $user->uuid;
        }

        return $request->store_uuid;
    }
}
