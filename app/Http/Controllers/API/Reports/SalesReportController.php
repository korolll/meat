<?php

namespace App\Http\Controllers\API\Reports;

use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportSalesReceiptsRequest;
use App\Http\Responses\ReportReceiptsSalesCollectionResponse;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Date;

class SalesReportController extends Controller
{
    /**
     * @param ReportSalesReceiptsRequest $request
     * @return \App\Services\Framework\Http\EloquentCollectionResponse
     * @throws \App\Exceptions\TealsyException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(ReportSalesReceiptsRequest $request)
    {
        $this->authorize('sales-report', Receipt::class);

        return ReportReceiptsSalesCollectionResponse::create($this->getQueryForIndex($request));
    }

    /**
     * @param ReportSalesReceiptsRequest $request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function export(ReportSalesReceiptsRequest $request)
    {
        $this->authorize('sales-report', Receipt::class);

        return (new SalesReportExport($this->getQueryForExport($request)->get()))->download('sales-report.xlsx');
    }

    /**
     * @param ReportSalesReceiptsRequest $request
     * @return mixed
     */
    protected function getQueryForExport(ReportSalesReceiptsRequest $request)
    {
        $dateStart = Date::parse($request->date_start);
        $dateEnd = Date::parse($request->date_end);
        $targetStore = $this->getTargetUser($request);

        $query = $targetStore->receipts()
            ->where('created_at', '>=', $dateStart)
            ->where('created_at', '<=', $dateEnd);

        return Receipt::query()
            ->fromSub($query->toBase(), 'receipts')
            ->with('receiptLines.assortment.images');
    }

    /**
     * @param ReportSalesReceiptsRequest $request
     * @return mixed
     */
    protected function getQueryForIndex(ReportSalesReceiptsRequest $request)
    {
        $dateStart = Date::parse($request->date_start);
        $dateEnd = Date::parse($request->date_end);

        $targetStore = $this->getTargetUser($request);
        $requestFilterWhere = $request->query('where', []);

        $query = $targetStore->receipts()
            ->select([
                'receipts.uuid',
                'receipts.user_uuid',
                'receipts.receipt_package_id',
                'receipts.id',
                'receipts.loyalty_card_uuid',
                'receipts.loyalty_card_type_uuid',
                'receipts.loyalty_card_number',
                'receipts.total',
                'receipts.created_at',
            ])
            ->join('receipt_lines', function (JoinClause $join) {
                $join->on('receipt_lines.receipt_uuid', '=', 'receipts.uuid');
            })
            ->join('assortments', function (JoinClause $join) use ($requestFilterWhere) {
                $join->on('receipt_lines.assortment_uuid', '=', 'assortments.uuid');
                foreach ($requestFilterWhere as $where) {
                    [$field, $operator, $value] = $where;

                    if ($field === 'assortment_name') {
                        $field = 'name';
                    }

                    switch ($operator) {
                        case 'in':
                            $join->whereIn("assortments.$field", (array) $value);
                            break;
                        case 'not in':
                            $join->whereNotIn("assortments.$field", (array) $value);
                            break;
                        case 'is null':
                            $join->whereNull("assortments.$field");
                            break;
                        case 'is not null':
                            $join->whereNotNull("assortments.$field");
                            break;
                        default:
                            if (is_array($value)) {
                                $value = current($value);
                            }
                            $join->where("assortments.$field", $operator, $value);
                    }
                }
            })
            ->where('receipts.created_at', '>=', $dateStart)
            ->where('receipts.created_at', '<=', $dateEnd);

        return Receipt::query()
            ->fromSub($query->toBase(), 'receipts')
            ->with(['receiptLines.assortment.images']);
    }

    /**
     * @param \App\Http\Requests\ReportSalesReceiptsRequest $request
     *
     * @return \App\Models\User
     */
    protected function getTargetUser(ReportSalesReceiptsRequest $request): User
    {
        $user = $this->user;
        $storeUuid = $request->store_uuid;
        if ($user->is_admin && $storeUuid) {
            return User::findOrFail($storeUuid);
        }

        return $user;
    }
}
