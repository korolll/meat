<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class ReceiptSummaryExport implements FromCollection, WithEvents
{
    use Exportable;

    /**
     * @var Collection
     */
    protected $receipts;

    /**
     * ReceiptSummaryExport constructor.
     * @param Collection $receipts
     */
    public function __construct(Collection $receipts)
    {
        $this->receipts = $receipts;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->receipts;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => [$this, 'beforeSheet'],
        ];
    }

    /**
     * @param BeforeSheet $event
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function beforeSheet(BeforeSheet $event): void
    {
        $event->sheet->append([
            [
                'Дата',
                'Количество',
                'Продажи',
            ],
        ]);

        $worksheet = $event->sheet->getDelegate();
        $worksheet->getColumnDimension('A')->setWidth(70);
        $worksheet->getColumnDimension('B')->setWidth(20);
        $worksheet->getColumnDimension('C')->setWidth(20);
        $worksheet->getStyle('A2:C2')->getFont()->setBold(true);
    }
}
