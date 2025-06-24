<?php

namespace App\Services\Management\Receipt;

use App\Contracts\Models\LoyaltyCard\CreateLoyaltyCardContract;
use App\Events\ReceiptReceived;
use App\Exceptions\ClientExceptions\ReceiptHasNoLinesException;
use App\Models\Assortment;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Services\Management\Receipt\Contracts\ReceiptFactoryContract;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Exception;

class ReceiptFactory implements ReceiptFactoryContract
{
    /**
     * @var CreateLoyaltyCardContract
     */
    protected $loyaltyCardFactory;

    /**
     * @var Receipt
     */
    protected $receipt;

    /**
     * @param CreateLoyaltyCardContract $loyaltyCardFactory
     */
    public function __construct(CreateLoyaltyCardContract $loyaltyCardFactory)
    {
        $this->loyaltyCardFactory = $loyaltyCardFactory;
    }

    /**
     * @param array $attributes
     * @return Receipt
     * @throws \App\Exceptions\TealsyException
     * @throws \Throwable
     */
    public function create(array $attributes)
    {
        list($receipt, $receiptLines) = $this->splitAttributes($attributes);

        if (empty($receiptLines)) {
            throw new ReceiptHasNoLinesException();
        }

        return DB::transaction(function () use ($receipt, $receiptLines) {
            return $this->transaction($receipt, $receiptLines);
        });
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function splitAttributes(array $attributes)
    {
        return [
            Arr::except($attributes, 'receipt_lines'),
            Arr::get($attributes, 'receipt_lines', []),
        ];
    }

    /**
     * @param array $receipt
     * @param array $receiptLines
     * @return Receipt
     */
    protected function transaction(array $receipt, array $receiptLines)
    {
        $this->receipt = $this->createReceipt($receipt);

        foreach ($receiptLines as $receiptLine) {
            $this->createReceiptLine($receiptLine);
        }

        ReceiptReceived::dispatch($this->receipt);

        return $this->receipt;
    }

    /**
     * @param array $attributes
     * @return Receipt
     */
    protected function createReceipt(array $attributes)
    {
        $receipt = new Receipt();
        $receipt->forceFill($attributes);

        if (($loyaltyCardUuid = $this->findLoyaltyCardUuid($attributes)) !== null) {
            $receipt->loyalty_card_uuid = $loyaltyCardUuid;
        }

        $receipt->save();

        return $receipt;
    }

    /**
     * @param array $attributes
     * @return ReceiptLine
     */
    protected function createReceiptLine(array $attributes)
    {
        $receiptLine = new ReceiptLine();
        $receiptLine->forceFill($attributes);

        if (($assortmentUuid = $this->findAssortmentUuid($attributes)) !== null) {
            $receiptLine->assortment_uuid = $assortmentUuid;
        }

        if (($productUuid = $this->findProductUuid($attributes)) !== null) {
            $receiptLine->product_uuid = $productUuid;
        }

        $receiptLine->receipt()->associate($this->receipt);
        $receiptLine->save();

        return $receiptLine;
    }

    /**
     * @param array $attributes
     * @return string|null
     */
    protected function findLoyaltyCardUuid(array $attributes)
    {
        if (empty($attributes['loyalty_card_type_uuid']) || empty($attributes['loyalty_card_number'])) {
            return null;
        }

        try {
            $first = $this->loyaltyCardFactory->create(
                $attributes['loyalty_card_type_uuid'],
                $attributes['loyalty_card_number']
            );
        } catch (Exception $e) {
            $first = null;
        }

        return $first ? $first->uuid : null;
    }

    /**
     * @param array $attributes
     * @return string|null
     */
    protected function findAssortmentUuid(array $attributes)
    {
        if (empty($attributes['barcode'])) {
            return null;
        }

        try {
            $first = Assortment::select('assortments.uuid')
                ->join('assortment_barcodes', 'assortment_barcodes.assortment_uuid', '=', 'assortments.uuid')
                // TODO Решить вопрос с весовыми EAN13 штрихкодами
                ->where('assortment_barcodes.barcode', $attributes['barcode'])
                ->toBase()
                ->first();
        } catch (QueryException $e) {
            $first = null;
        }

        return $first ? $first->uuid : null;
    }

    /**
     * @param array $attributes
     * @return string|null
     */
    protected function findProductUuid(array $attributes)
    {
        if (empty($attributes['barcode'])) {
            return null;
        }

        try {
            $first = Product::select('products.uuid')
                ->join('assortments', 'assortments.uuid', '=', 'products.assortment_uuid')
                ->join('assortment_barcodes', 'assortment_barcodes.assortment_uuid', '=', 'assortments.uuid')
                ->where('products.user_uuid', $this->receipt->user_uuid)
                // TODO Решить вопрос с весовыми EAN13 штрихкодами
                ->where('assortment_barcodes.barcode', $attributes['barcode'])
                ->toBase()
                ->first();
        } catch (QueryException $e) {
            $first = null;
        }

        return $first ? $first->uuid : null;
    }
}
