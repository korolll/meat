<?php

namespace App\Http\Requests;

use App\Models\AssortmentVerifyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssortmentVerifyRequest extends FormRequest
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
            'assortment_verify_status_id' => [
                'required',
                Rule::in([AssortmentVerifyStatus::ID_APPROVED, AssortmentVerifyStatus::ID_DECLINED]),
            ],
        ];
    }
}
