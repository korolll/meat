<?php

namespace App\Http\Requests;

use App\Models\Assortment;
use App\Models\Catalog;
use App\Models\FileCategory;
use App\Rules\FileExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class CatalogStoreRequest extends FormRequest
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
            'name' => 'required|string|between:2,60',
            'sort_number' => 'nullable|integer|between:1,9999',
            'image_uuid' => [
                'nullable',
                'uuid',
                new FileExists(FileCategory::ID_CATALOG_IMAGE),
            ],
            'catalog_uuid' => [
                'nullable',
                'uuid',
                'exists:catalogs,uuid',
                function ($attribute, $value, $fail) {
                    if (Assortment::query()->where('catalog_uuid', '=', $value)->exists()) {
                        $fail($attribute . ' contains assortments.');
                    }

                },
                function ($attribute, $value, $fail) {
                    if ($this->catalog) {

                        $from = DB::raw("catalog_with_all_parents('{$value}') as catalogs");

                        if (Catalog::query()->from($from)->where('uuid', '=', $this->catalog->uuid)->exists()) {
                            $fail('The' . $attribute . ' cannot be a parent');
                        }
                    }
                }
            ],
        ];
    }
}
