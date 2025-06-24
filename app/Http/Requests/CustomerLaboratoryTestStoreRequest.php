<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Models\LaboratoryTestStatus;
use App\Rules\Uuid;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerLaboratoryTestStoreRequest extends FormRequest
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
        $statuses = [
            LaboratoryTestStatus::ID_NEW,
            LaboratoryTestStatus::ID_CREATED
        ];

        $req = 'required_if:laboratory_test_status_id,' . LaboratoryTestStatus::ID_NEW;
        return [
            'laboratory_test_status_id' => ['required', Rule::in($statuses)],
            'laboratory_test_appeal_type_uuid' => [$req, 'nullable', new Uuid(), 'exists:laboratory_test_appeal_types,uuid'],
            'assortment_supplier_user_uuid' => [$req, 'nullable', new Uuid(), 'exists:users,uuid'],

            'customer_full_name' => $req . '|nullable|string',
            'customer_organization_name' => $req . '|nullable|min:5',
            'customer_organization_address' => $req . '|nullable|string|min:5',
            'customer_inn' => $req. '|nullable|string|digits_between:10,12',
            'customer_kpp' => 'nullable|string|digits:9',
            'customer_ogrn' => $req . '|nullable|string|digits_between:13,15',

            'customer_position' => 'nullable|string|min:5',
            'customer_bank_correspondent_account' => 'nullable|string|digits:20',
            'customer_bank_current_account' => 'nullable|string|digits:20',
            'customer_bank_identification_code' => 'nullable|string|digits:9',
            'customer_bank_name' => 'nullable|string|min:5',

            'assortment_barcode' => $req . '|nullable|digits:13',
            'assortment_uuid' => [$req, 'nullable', new Uuid(), 'exists:assortments,uuid'],
            'assortment_name' => $req . '|nullable|string|between:2,160',
            'assortment_manufacturer' => $req . '|nullable|string|between:2,60',
            'assortment_production_standard_id' => $req . '|nullable|exists:production_standards,id',

            'batch_number' => $req . '|nullable|string|between:3,255',
            'parameters' => $req . '|nullable|string|min:3',

            'customer_files' => 'array|between:0,50',
            'customer_files.*.uuid' => [
                'required',
                'distinct',
                new Uuid(),
                Rule::exists('files', 'uuid')->where(function (Builder $query) {
                    $query->where('file_category_id', FileCategory::ID_LABORATORY_TEST_FILE_CUSTOMER);

                    if (!user()->is_admin) {
                        $query->where('user_uuid', user()->uuid);
                    }

                    return $query;
                }),
            ],
            'customer_files.*.public_name' => 'nullable|string|between:0,255'
        ];
    }
}
