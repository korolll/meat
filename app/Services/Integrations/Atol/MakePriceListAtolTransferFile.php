<?php

namespace App\Services\Integrations\Atol;

use App\Models\AssortmentUnit;
use App\Models\PriceList;
use App\Models\Product;

class MakePriceListAtolTransferFile extends AtolTransferFile
{
    /**
     * @var PriceList
     */
    private $priceList;

    /**
     * @param PriceList $priceList
     */
    public function __construct(PriceList $priceList)
    {
        $this->priceList = $priceList->loadMissing([
            'activeProducts.assortment',
        ]);

        $this->makeContents();
    }

    /**
     * @return void
     */
    private function makeContents(): void
    {
        $this->command('DELETEALLWARES');
        $this->command('DELETEALLBARCODES');
        $this->command('DELETEALLASPECTSCHMS');
        $this->command('DELETEALLTAXGROUPRATES');
        $this->command('DELETEALLTAXGROUPS');
        $this->command('DELETEALLTAXRATES');
        $this->command('DELETEALLEMPLOYEES');

        $this->command('ADDTAXRATES', [
            '1;Без НДС;Без НДС;0;0;',
            '2;10 %;10 %;0;10;',
            '3;20 %;20 %;0;20;',
        ]);

        $this->command('ADDTAXGROUPS', [
            '1;Без НДС;Без НДС',
            '2;10 %;10 %',
            '3;20 %;20 %',
        ]);

        $this->command('ADDTAXGROUPRATES', [
            '1;1;1;0',
            '2;2;2;0',
            '3;3;3;0',
        ]);

        $this->command('ADDQUANTITY', $this->getQuantities());
        $this->command('ADDBARCODES', $this->getBarcodes());
        $this->command('DELETEALLUSERS');

        $this->command('ADDUSERS', [
            '1;Кассир;;;;;',
        ]);
    }

    /**
     * @return array
     */
    private function getQuantities(): array
    {
        return $this->getDataWithBarcode('makeQuantity');
    }

    /**
     * @return array
     */
    private function getBarcodes(): array
    {
        return $this->getDataWithBarcode('makeBarcode');
    }

    /**
     * @param $makeFuncName
     * @return array
     */
    private function getDataWithBarcode($makeFuncName)
    {
        $result = [];
        foreach ($this->priceList->activeProducts->all() as $product) {
            foreach ($this->{$makeFuncName}($product) as $row) {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * @param Product $product
     * @return \Generator|array
     */
    private function makeQuantity(Product $product)
    {
        foreach ($product->assortment->barcodes->pluck('barcode') as $barcode) {
            $quantity = array_fill(1, 57, null);

            $quantity[1] = $barcode;
            $quantity[3] = $product->assortment->name;
            $quantity[5] = $product->pivot->price_new;
            $quantity[6] = $product->quantity;
            $quantity[8] = str_repeat('0', 14);
            $quantity[23] = $this->getTaxGroupCode($product->assortment->nds_percent);
            $quantity[33] = 1;

            if ($product->assortment->assortment_unit_id === AssortmentUnit::ID_KILOGRAM) {
                $quantity[8][0] = '1';
            }

            yield $quantity;
        }
    }

    /**
     * @param Product $product
     * @return \Generator|array
     */
    private function makeBarcode(Product $product)
    {
        foreach ($product->assortment->barcodes->pluck('barcode') as $barcode) {
            $item = array_fill(1, 5, null);

            $item[1] = $barcode;
            $item[2] = $barcode;
            $item[4] = 1;

            yield $item;
        }
    }

    /**
     * @param int $ndsPercent
     * @return int
     */
    protected function getTaxGroupCode(int $ndsPercent): int
    {
        switch ($ndsPercent) {
            case 10:
                return 2;
            case 20:
                return 3;
        }

        return 1;
    }
}
