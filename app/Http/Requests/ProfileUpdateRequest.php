<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Models\LegalForm;
use App\Models\User;
use App\Models\UserType;
use App\Rules\AlphaSpace;
use App\Rules\FileExists;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'user_type_id' => ['required', Rule::in(UserType::USER_AVAILABLE_IDS)],
            'full_name' => ['required', 'string', 'between:5,60', new AlphaSpace()],
            'legal_form_id' => ['required', Rule::in(LegalForm::USER_AVAILABLE_IDS)],
            'organization_name' => 'required|min:5',
            'organization_address' => 'required|string|min:5',
            'address' => 'required|string|min:5',
            'phone' => ['required', new PhoneNumber()],
            'password' => 'nullable|string|min:6',
            'inn' => 'required|string|digits_between:10,12',
            'kpp' => 'required_unless:legal_form_id,ip|nullable|string|digits:9',
            'ogrn' => 'required|string|digits_between:13,15',
            'region_uuid' => 'nullable|uuid|exists:regions,uuid',
            'position' => 'nullable|string|min:5',
            'bank_correspondent_account' => 'nullable|string|digits:20',
            'bank_current_account' => 'nullable|string|digits:20',
            'bank_identification_code' => 'nullable|string|digits:9',
            'bank_name' => 'nullable|string|min:5',

            'additional_emails' => 'array|min:0',
            'additional_emails.*' => 'between:5,50|email',

            'files' => 'array|between:0,50',
            'files.*.uuid' => [
                'required',
                'distinct',
                'uuid',
                new FileExists(FileCategory::ID_USER_FILE),
            ],
            'files.*.public_name' => 'nullable|string|between:0,255',

            'less_zone_distance' => 'nullable|integer',
            'between_zone_distance' => 'nullable|integer',
            'more_zone_distance' => 'nullable|integer',
            'max_zone_distance' => 'nullable|integer',
            'less_zone_price' => 'nullable|numeric',
            'between_zone_price' => 'nullable|numeric',
            'more_zone_price' => 'nullable|numeric',
        ];

        /** @var User $user */
        $user = $this->user();

        if ($user->is_store || $user->is_admin) {
            $rules = array_merge($rules, [
                'address_latitude' => 'nullable|numeric|between:-90,90',
                'address_longitude' => 'nullable|numeric|between:-180,180',
                'work_hours_from' => 'nullable|string|date_format:H:i|before:work_hours_till',
                'work_hours_till' => 'nullable|string|date_format:H:i|after:work_hours_from',
                'brand_name' => 'nullable|string|between:2,60',

                'signer_type_id' => 'nullable|exists:signer_types,id',
                'signer_full_name' => 'nullable|string',
                'power_of_attorney_number' => 'nullable|string',
                'date_of_power_of_attorney' => 'nullable|string|date',
                'ip_registration_certificate_number' => 'nullable|string',
                'date_of_ip_registration_certificate' => 'nullable|string|date',

                'has_parking' => 'nullable|boolean',
                'has_ready_meals' => 'nullable|boolean',
                'has_atms' => 'nullable|boolean',
                'image_uuid' => [
                    'nullable',
                    'uuid',
                    new FileExists(FileCategory::ID_SHOP_IMAGE),
                ],
                'allow_find_nearby' => 'nullable|boolean',
            ]);
        }

        return $rules;
    }

    /**
     * @return string[]
     */
    public function getAdditionalEmails(): array
    {
        return Arr::get($this, 'additional_emails', []);
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return collect(Arr::get($this, 'files', []))->mapWithKeys(function ($file) {
            return [
                $file['uuid'] => [
                    'public_name' => Arr::get($file, 'public_name', null),
                ],
            ];
        })->all();
    }
}
