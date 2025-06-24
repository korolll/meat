<?php

namespace App\Services\Documents\Spreadsheets;

use App\Models\Product;
use App\Models\ProductRequest;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierProductRequestSpreadsheet extends Spreadsheet
{
    /**
     * @var ProductRequest
     */
    protected $productRequest;

    /**
     * @var Worksheet
     */
    protected $sheet;

    /**
     * @param ProductRequest $productRequest
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function __construct(ProductRequest $productRequest)
    {
        parent::__construct();

        $this->productRequest = $productRequest;
        $this->sheet = $this->getSheet($this->getFirstSheetIndex());

        $this->applyStyles();
        $this->fillSheet();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function applyStyles()
    {
        $this->sheet->getColumnDimension('A')->setWidth(20);
        $this->sheet->getColumnDimension('B')->setWidth(70);

        $this->sheet->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 24]]);
        $this->sheet->getStyle('A2')->applyFromArray(['font' => ['size' => 16]]);
        $this->sheet->getStyle('A6')->applyFromArray(['font' => ['bold' => true]]);
        $this->sheet->getStyle('A8:D8')->applyFromArray(['font' => ['bold' => true]]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function fillSheet()
    {
        $supplierOrganizationName = $this->productRequest->supplierUser->organization_name;
        $customerOrganizationName = $this->productRequest->customerUser->organization_name;
        $customerAddress = $this->productRequest->customerUser->address;
        $onDate = $this->productRequest->expected_delivery_date->addDay()->format('d.m.Y');
        $onWarehouseDate = $this->productRequest->expected_delivery_date->format('d.m.Y');
        $createdAt = $this->productRequest->created_at->format('d.m.Y');

        $this->sheet->setCellValue('A1', $supplierOrganizationName);
        $this->sheet->setCellValue('A2', "Общий заказ товара от {$createdAt}");

        $this->sheet->setCellValue('A4', "{$customerOrganizationName}, адрес: {$customerAddress}");

        $this->sheet->setCellValue('A6', 'На дату');
        $this->sheet->setCellValue('B6', "{$onDate} (на склад {$onWarehouseDate})");

        $this->sheet->setCellValue('A8', 'Штрих-Код');
        $this->sheet->setCellValue('B8', 'Наименование');
        $this->sheet->setCellValue('C8', 'Ед. изм.');
        $this->sheet->setCellValue('D8', 'Кол-во');

        $this->sheet->fromArray($this->makeTable(), null, 'A9');
    }

    /**
     * @return array
     */
    protected function makeTable(): array
    {
        $products = $this->productRequest->products()
            ->select('products.*')
            ->join('assortments', 'products.assortment_uuid', '=', 'assortments.uuid')
            ->orderBy('assortments.name')
            ->with(['assortment.assortmentUnit', 'assortment.barcodes'])
            ->get();

        return $products->map(function (Product $product) {
            return [
                $product->assortment->barcodes->pluck('barcode')->implode(', ') . ' ',
                $product->assortment->name,
                $product->assortment->assortmentUnit->short_name,
                $product->pivot->quantity,
            ];
        })->all();
    }
}
