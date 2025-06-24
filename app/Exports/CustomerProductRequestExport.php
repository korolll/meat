<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\ProductRequest;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events;

class CustomerProductRequestExport implements FromCollection, WithEvents, WithMapping
{
    use Exportable;

    /**
     * @var ProductRequest
     */
    private $request;

    /**
     * @param ProductRequest $request
     */
    public function __construct(ProductRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->request->products()->with('assortment')->get();
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
                'Дата создания',
                $this->request->created_at,
            ],
            [
                'Инициатор заявки',
                $this->request->customerUser->organization_name,
            ],
            [
                'Адрес инициатора',
                $this->request->customerUser->organization_address,
            ],
            [
                'Исполнитель заявки',
                $this->request->supplierUser->organization_name,
            ],
            [
                'Адрес исполнителя',
                $this->request->supplierUser->organization_address,
            ],
            [
                //
            ],
            [
                'Штрихкод',
                'Название товара',
                'Количество товара',
                'Цена товара закупочная',
                'Цена товара рекомендуемая розничная',
            ],
        ]);

        $worksheet = $event->sheet->getDelegate();
        $worksheet->getColumnDimension('A')->setWidth(20);
        $worksheet->getColumnDimension('B')->setWidth(70);
        $worksheet->getColumnDimension('C')->setWidth(20);
        $worksheet->getColumnDimension('D')->setWidth(20);
        $worksheet->getColumnDimension('E')->setWidth(20);
        $worksheet->getStyle('A7:E7')->getFont()->setBold(true);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function map($product): array
    {
        return [
            // @todo подумать как переделать
            $product->assortment->barcodes->implode('barcode', ' ,') . ' ',
            $product->assortment->name,
            $product->pivot->quantity,
            $product->pivot->price,
            $product->price_recommended,
        ];
    }
}
