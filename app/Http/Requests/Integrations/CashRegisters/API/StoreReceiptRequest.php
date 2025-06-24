<?php

namespace App\Http\Requests\Integrations\CashRegisters\API;

use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;

class StoreReceiptRequest extends CalculateReceiptRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules['loyalty_card_uuid'] = 'uuid|exists:loyalty_cards,uuid';
        $rules['receipt_id'] = 'integer';
        $rules['receipt_package_id'] = 'integer';
        $rules['uuid'] = 'uuid';

        $rule = Rule::exists('receipts', 'uuid');
        $storeUuid = $this->store_uuid;
        if (is_scalar($storeUuid) && Uuid::isValid((string)$storeUuid)) {
            $rule->where('user_uuid', $storeUuid);
        }

        $rules['refund_by_receipt_uuid'] = ['uuid', $rule];
        return $rules;
    }
}
