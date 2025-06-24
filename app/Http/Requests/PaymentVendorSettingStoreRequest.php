<?php

namespace App\Http\Requests;

use App\Models\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentVendorSettingStoreRequest extends FormRequest
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
            'payment_vendor_id' => 'required|string|exists:payment_vendors,id',
            'config' => 'required|array|min:1',
            'stores' => 'required|array|min:0',
            'stores.*.store_uuid' => [
                'required',
                'uuid',
                Rule::exists('users', 'uuid')
                    ->where('user_type_id', UserType::ID_STORE)
            ],
            'stores.*.is_active' => 'required|boolean',
        ];
    }
}
