<?php

namespace App\Http\Requests\Clients\API;

use App\Models\UserType;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone' => ['required', new PhoneNumber(), "unique:clients,phone,{$this->getProfileUuid()},uuid,deleted_at,NULL"],
            'name' => 'nullable|string|min:2|max:60',
            'sex' => 'nullable|string|in:male,female',
            'email' => 'nullable|string|email',
            'birth_date' => 'nullable|string|date',
            'consent_to_service_newsletter' => 'nullable|boolean',
            'consent_to_receive_promotional_mailings' => 'nullable|boolean',
            'is_agree_with_diverse_food_promo' => 'boolean',
            'app_version' => 'nullable|string|min:1|max:20',
            'selected_store_user_uuid' => [
                'nullable',
                'uuid',
                Rule::exists('users', 'uuid')
                    ->where('user_type_id', UserType::ID_STORE)
            ],
        ];
    }

    /**
     * @return string
     */
    private function getProfileUuid()
    {
        return $this->user()->uuid;
    }
}
