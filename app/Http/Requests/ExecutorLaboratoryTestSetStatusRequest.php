<?php

namespace App\Http\Requests;

use App\Models\FileCategory;
use App\Rules\Uuid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

class ExecutorLaboratoryTestSetStatusRequest extends FormRequest
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
            'laboratory_test_status_id' => 'required|string|exists:laboratory_test_statuses,id',
            'executor_files' => 'required_if:laboratory_test_status_id,done|nullable|array|between:1,50',
            'executor_files.*.uuid' => [
                'required',
                'distinct',
                new Uuid(),
                Rule::exists('files', 'uuid')->where(function (Builder $query) {
                    $query->where('file_category_id', FileCategory::ID_LABORATORY_TEST_FILE_EXECUTOR);

                    if (!user()->is_admin) {
                        $query->where('user_uuid', user()->uuid);
                    }

                    return $query;
                }),
            ],
            'executor_files.*.public_name' => 'nullable|string|between:0,255'
        ];
    }
}
