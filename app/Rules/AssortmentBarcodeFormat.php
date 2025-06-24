<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AssortmentBarcodeFormat implements Rule
{
    /**
     * @var bool
     */
    private $isWeightBarcode;

    /**
     * @param bool $isWeightBarcode
     */
    public function __construct(bool $isWeightBarcode)
    {
        $this->isWeightBarcode = $isWeightBarcode;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match($this->makePregPattern(), $value) > 0;
    }

    /**
     * @return string
     */
    public function message()
    {
        return trans('validation.regex');
    }

    /**
     * @return string
     */
    private function makePregPattern(): string
    {
        return $this->isWeightBarcode ? '/^\d{7}[0]{6}$/' : '/^(\d{13}|\d{8})$/';
    }
}
