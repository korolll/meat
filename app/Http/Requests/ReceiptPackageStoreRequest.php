<?php

namespace App\Http\Requests;

use App\Models\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptPackageStoreRequest extends FormRequest
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
        return [
            '@type' => 'required|in:ReceiptPackage',
            'createDate' => 'required|string|date', // @todo iso rule
            'packageId' => 'required|integer|min:1',
            'userUuid' => [
                'required',
                'uuid',
                Rule::exists('users', 'uuid')->where('user_type_id', UserType::ID_STORE),
            ],
            'data' => 'required|array',
            'data.*.date' => 'required|string|date', // @todo iso rule
            'data.*.num' => 'required|integer|min:1',
            'data.*.loyaltyCardNumber' => 'nullable|digits_between:3,20',
            'data.*.loyaltyCardTypeUuid' => 'nullable|uuid|exists:loyalty_card_types,uuid',
            'data.*.sum' => 'required|numeric|between:0,99999999.99',
            'data.*.productList' => 'required|array',
            'data.*.productList.*.barcode' => 'required|digits:13',
            'data.*.productList.*.sum' => 'required|numeric|between:0,99999999.99',
            'data.*.productList.*.quantity' => 'required|numeric|min:0.01',
        ];
    }

    /**
     * @return array
     */
    public function getRawReceipts()
    {
        return array_map([$this, 'processReceipt'], $this->data);
    }

    /**
     * @param array $item
     * @return array
     */
    private function processReceipt($item)
    {
        return [
            'user_uuid' => $this['userUuid'],
            'receipt_package_id' => $this['packageId'],
            'id' => $item['num'],
            'loyalty_card_type_uuid' => $item['loyaltyCardTypeUuid'],
            'loyalty_card_number' => $item['loyaltyCardNumber'],
            'total' => $item['sum'],
            'created_at' => $item['date'],
            'receipt_lines' => array_map([$this, 'processReceiptLine'], $item['productList']),
        ];
    }

    /**
     * @param array $item
     * @return array
     */
    private function processReceiptLine($item)
    {
        return [
            'barcode' => $item['barcode'],
            'quantity' => $item['quantity'],
            'total' => $item['sum'],
        ];
    }
}
