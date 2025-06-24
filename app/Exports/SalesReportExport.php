<?php

namespace App\Exports;

use App\Models\Receipt;
use App\Models\ReceiptLine;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class SalesReportExport implements FromCollection, WithEvents
{
    use Exportable;

    /**
     * @var Collection
     */
    protected $receipts;

    /**
     * ReceiptSummaryExport constructor.
     * @param Collection|Receipt[] $receipts
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
        // Сформировать поля

//        return $this->receipts;
        return $this->receipts->map(static function (Receipt $receipt) {
            return $receipt->receiptLines->map(static function (ReceiptLine $receiptLine) use ($receipt) {
                if ($receiptLine->price_with_discount) {
                    $price = $receiptLine->price_with_discount;
                    $originalPrice = $receiptLine->price_with_discount + $receiptLine->discount;
                    $totalDiscount = $receiptLine->discount * $receiptLine->quantity;
                } else {
                    $price = $receiptLine->total / $receiptLine->quantity;
                    $originalPrice = '';
                    $totalDiscount = '';
                }

                /**
                 * @var $receiptLine ReceiptLine
                 */
                return [
                    (string) $receipt->id,
                    $receiptLine->assortment->name,
                    $receiptLine->assortment->barcodes->pluck('barcode')->implode(', '),

                    (string) $price,
                    (string) $originalPrice,
                    (string) $receiptLine->discount,

                    (string) $receiptLine->quantity,
                    (string) $receiptLine->total,
                    (string) $totalDiscount,

                    (string) $receipt->created_at
                ];
            });
        });
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
                '№ Чека',
                'Наименование номенклатуры',
                'Штрихкод',

                'Цена',
                'Оригинальная цена',
                'Размер скидки',

                'Количество',
                'Общая стоимость',
                'Общая скидка',

                'Дата'
            ]
        ]);

        $worksheet = $event->sheet->getDelegate();
        $worksheet->getColumnDimension('A')->setWidth(20);
        $worksheet->getColumnDimension('B')->setWidth(90);
        $worksheet->getColumnDimension('C')->setWidth(20);
        $worksheet->getColumnDimension('D')->setWidth(15);
        $worksheet->getColumnDimension('E')->setWidth(15);
        $worksheet->getColumnDimension('F')->setWidth(15);
        $worksheet->getColumnDimension('G')->setWidth(15);
        $worksheet->getColumnDimension('H')->setWidth(25);
        $worksheet->getColumnDimension('I')->setWidth(25);
        $worksheet->getColumnDimension('J')->setWidth(25);
        $worksheet->getStyle('A2:J2')->getFont()->setBold(true);
    }
}
