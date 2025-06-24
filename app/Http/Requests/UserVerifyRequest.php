<?php

namespace App\Http\Requests;

use App\Models\UserVerifyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserVerifyRequest extends FormRequest
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
            'user_verify_status_id' => [
                'required',
                Rule::in([UserVerifyStatus::ID_APPROVED, UserVerifyStatus::ID_DECLINED]),
            ],
            'comment' => [
                'required_if:user_verify_status_id,' . UserVerifyStatus::ID_DECLINED,
                'nullable',
                'string'
            ]
        ];
    }
}
