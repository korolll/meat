<?php

namespace App\Exports;

use App\Models\PriceList;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events;

class PriceListExport implements FromCollection, WithEvents, WithMapping, WithStrictNullComparison
{
    use Exportable;

    /**
     * @var PriceList
     */
    private $priceList;

    /**
     * @param PriceList $priceList
     */
    public function __construct(PriceList $priceList)
    {
        $this->priceList = $priceList;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->priceList->activeProducts()
            ->select('products.*')
            ->join('assortments', 'products.assortment_uuid', '=', 'assortments.uuid')
            ->orderBy('assortments.name')
            ->with('assortment')
            ->get();
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            Events\BeforeSheet::class => [$this, 'beforeSheet'],
        ];
    }

    /**
     * @param Events\BeforeSheet $event
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function beforeSheet(Events\BeforeSheet $event): void
    {
        $event->sheet->append([
            [
                $this->priceList->name,
            ],
            [
                'Действует от',
                $this->priceList->date_from ?? '-',
            ],
            [
                'Действует до',
                $this->priceList->date_till ?? '-',
            ],
            [
                //
            ],
            [
                'Штрихкод',
                'Название товара',
                'Цена товара',
                'НДС, %',
                'Квант, шт',
            ],
        ]);

        $worksheet = $event->sheet->getDelegate();
        $worksheet->getColumnDimension('A')->setWidth(20);
        $worksheet->getColumnDimension('B')->setWidth(70);
        $worksheet->getColumnDimension('C')->setWidth(20);
        $worksheet->getColumnDimension('D')->setWidth(10);
        $worksheet->getColumnDimension('E')->setWidth(10);
        $worksheet->getStyle('A1')->getFont()->setBold(true);
        $worksheet->getStyle('A5:E5')->getFont()->setBold(true);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function map($product): array
    {
        return [
            $product->assortment->barcodes->pluck('barcode')->implode(', ') . ' ',
            $product->assortment->name,
            $product->pivot->price_new,
            $product->assortment->nds_percent,
            $product->quantum,
        ];
    }
}
