<?php

namespace App\Rules;

use App\Models\Assortment;
use App\Models\AssortmentUnit;
use App\Models\AssortmentVerifyStatus;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AssortmentBarcodeUnique implements Rule
{
    /**
     * @var bool
     */
    private $isWeightBarcode;

    /**
     * @var string|null
     */
    private $uuidToIgnore;

    /**
     * @param bool $isWeightBarcode
     * @param string|string[]|null $uuidToIgnore
     */
    public function __construct(bool $isWeightBarcode, $uuidToIgnore = null)
    {
        $this->isWeightBarcode = $isWeightBarcode;
        $this->uuidToIgnore = $uuidToIgnore;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->makeQuery($value)->exists() === false;
    }

    /**
     * @return string
     */
    public function message()
    {
        return trans('validation.unique');
    }

    /**
     * @param string $barcode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function makeQuery(string $barcode): Builder
    {
        $query = Assortment::query()
            ->select(['assortments.uuid'])
            ->join('assortment_barcodes', 'assortment_barcodes.assortment_uuid', '=', 'assortments.uuid')
            ->where('assortments.assortment_verify_status_id', '=', AssortmentVerifyStatus::ID_DECLINED);

        if (strlen($barcode) > 8) {
            $query->where('assortment_barcodes.barcode', 'like', $this->makeLikePattern($barcode));
        } else {
            $query->where('assortment_barcodes.barcode', $barcode);
        }

        if ($this->isWeightBarcode === false) {
            $query->where('assortments.assortment_unit_id', '=', AssortmentUnit::ID_KILOGRAM);
        }

        if ($this->uuidToIgnore !== null) {
            $query->whereNotIn('assortments.uuid', (array) $this->uuidToIgnore);
        }

        return $query;
    }

    /**
     * @param string $barcode
     * @return string
     */
    private function makeLikePattern(string $barcode): string
    {
        return Str::substr($barcode, 0, 7) . str_repeat('_', 6);
    }
}
