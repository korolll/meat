<?php

namespace App\Console\Commands;

use App\Models\Receipt;
use App\Models\ReceiptLine;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DeleteOldReceiptsWithoutCard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'receipts:delete-old-without-card';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаление старых чеков без карты';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysOld = Carbon::now()->startOfDay()->subDays(7);
        DB::transaction(function () use ($daysOld) {
            $receiptLineQuery = ReceiptLine::query()->join('receipts', 'receipts.uuid', '=', 'receipt_lines.receipt_uuid');
            $this->applyFilters($receiptLineQuery, $daysOld)->delete();

            $receiptQuery = Receipt::query();
            $this->applyFilters($receiptQuery, $daysOld)->delete();
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon                        $daysOld
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilters(Builder $query, Carbon $daysOld)
    {
        return $query
            ->where('receipts.created_at', '<', $daysOld)
            ->whereNull('receipts.loyalty_card_uuid')
            ->whereNull('receipts.loyalty_card_number');
    }
}
