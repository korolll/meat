<?php

namespace App\Http\Requests;

use App\Models\Assortment;
use App\Models\AssortmentProperty;
use App\Models\AssortmentPropertyDataType;
use App\Models\AssortmentUnit;
use App\Models\Catalog;
use App\Models\FileCategory;
use App\Rules\AssortmentBarcodeFormat;
use App\Rules\AssortmentBarcodeUnique;
use App\Rules\FileExists;
use App\Rules\NdsPercent;
use App\Rules\PublicCatalogExists;
use App\Structures\Models\Assortment\SavingAssortmentStructure;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Class AssortmentStoreRequest
 * @property $barcodes array
 *
 * @package App\Http\Requests
 */
class AssortmentStoreRequest extends FormRequest
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
        $user = $this->user();
        /** @var Assortment|null $assortment */
        $assortment = $this->assortment;

        if ($this->exists('*RequestFilters')){
            return [
                '*RequestFilters' => 'array',
            ];
        }

        $articleUniq = 'unique:assortments,article';
        if ($assortment) {
            $articleUniq .= ",$assortment->uuid,uuid";
        }

        $rules = [
            'catalog_uuid' => [
                'required',
                'uuid',
                new PublicCatalogExists(),
                function ($attribute, $value, $fail) {
                    if (Catalog::query()->where('catalog_uuid', '=', $value)->exists()) {
                        $fail($attribute . ' contains children.');
                    }
                },
            ],
            'name' => 'required|string|between:2,160',
            'short_name' => 'nullable|string|between:2,40',
            'assortment_unit_id' => 'required|exists:assortment_units,id',
            'country_id' => 'required|string|exists:countries,id',
            'okpo_code' => 'nullable|string|digits_between:5,10',
            'weight' => 'required|numeric|between:0,99999999.99',
            'volume' => 'nullable|numeric|between:0,99999999999.99',
            'manufacturer' => 'nullable|string|between:2,60',
            'ingredients' => 'nullable|string|between:2,3000',
            'description' => 'nullable|string|between:2,3000',
            'group_barcode' => 'nullable|string|between:5,200',
            'temperature_min' => 'nullable|integer|between:-100,' . ($this->temperature_max ?: 100),
            'temperature_max' => 'required|integer|between:-100,100',
            'production_standard_id' => 'required|string|exists:production_standards,id',
            'production_standard_number' => 'required|string|between:5,60',
            'is_storable' => 'required|boolean',
            'shelf_life' => 'required|integer|digits_between:1,5',
            'nds_percent' => ['required', 'numeric', new NdsPercent()],
            'assortment_brand_uuid' => 'nullable|uuid|exists:assortment_brands,uuid',
            'article' => 'nullable|string|between:1,255|' . $articleUniq,

            'images' => 'required|array|between:1,5',
            'images.*.uuid' => [
                'required',
                'distinct',
                'uuid',
                new FileExists(FileCategory::ID_ASSORTMENT_IMAGE),
            ],
            'images.*.public_name' => 'nullable|string|between:2,60',

            'files' => [
                'array',
                'between:0,50',
                function ($attribute, $value, $fail) use ($user, $assortment) {
                    if ($assortment !== null && !$user->is_admin) {
                        $fail("Not authorized to set '{$attribute}' field");
                    }
                },
            ],
            'files.*.uuid' => [
                'required',
                'distinct',
                'uuid',
                new FileExists(FileCategory::ID_ASSORTMENT_FILE),
            ],
            'files.*.public_name' => 'nullable|string|between:0,255',

            'properties' => 'present|array',
            'properties.*.uuid' => [
                'required',
                'distinct',
                'uuid',
                Rule::exists('assortment_property_catalog', 'assortment_property_uuid')
                    ->where(function (Builder $query) {
                        return $query->where('catalog_uuid', $this->catalog_uuid);
                    }),
            ],
            'properties.*.value' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user, $assortment) {
                    $id = data_get($this, str_replace('value', 'uuid', $attribute));
                    $property = AssortmentProperty::find($id);
                    switch ($property->assortment_property_data_type_id) {
                        case AssortmentPropertyDataType::ID_NUMBER:
                            $rule = 'numeric';
                            break;
                        case AssortmentPropertyDataType::ID_ENUM:
                            $rule = Rule::in($property->available_values);
                            break;
                        default:
                            $rule = 'between:2,255';
                            break;
                    }

                    $array = [];
                    Arr::set($array, $attribute, $value);
                    $validator = Validator::make($array, [$attribute => $rule]);
                    if ($validator->fails()) {
                        $fail($validator->getMessageBag()->get($attribute));
                    }
                },
            ],

            'tags' => 'nullable|array',
            'tags.*' => 'string|between:3,255',

            'barcodes' => 'nullable|array',
            'barcodes.*' => $this->makeBarcodeRules(),

            'bonus_percent' => 'nullable|numeric|between:0.00,100.00'
        ];

        if ($assortment === null || $user->is_admin) {
            $rules = array_merge($rules, [
                'declaration_end_date' => 'nullable|date'
            ]);
        }

        return $rules;
    }

    /**
     * @return array
     */
    private function makeBarcodeRules(): array
    {
        $isWeightBarcode = $this->assortment_unit_id === AssortmentUnit::ID_KILOGRAM;

        $rule = [
            'required',
            new AssortmentBarcodeFormat($isWeightBarcode),
            new AssortmentBarcodeUnique($isWeightBarcode),
        ];

        return $rule;
    }

    /**
     * @return \App\Models\Assortment|null
     * @see \App\Http\Controllers\API\AssortmentController::store
     */
    private function detectAssortment(): ?Assortment
    {
        $result = null;

        if ($this->assortment) {
            $result = $this->assortment;
        }

        if ($this->barcodes !== null) {
            $result = Assortment::join('assortment_barcodes', 'assortment_barcodes.assortment_uuid', '=', 'assortments.uuid')
                ->whereIn('assortment_barcodes.barcode', $this->barcodes)
                ->first();
        }

        return $result;
    }

    /**
     * @return SavingAssortmentStructure
     */
    public function asSaveData(): SavingAssortmentStructure
    {
        $validated = $this->validated();

        return app(SavingAssortmentStructure::class, [
            'parameters' => [
                'attributes' => $validated,
                'images' => $this->getImages($validated),
                'files' => $this->getFiles($validated),
                'properties' => $this->getProperties($validated),
                'tags' => $this->tags,
                'barcodes' => $this->barcodes ?: [],
                'forceSyncFiles' => $this->user()->is_admin
            ]
        ]);
    }

    /**
     * @param array $validated
     * @return array
     */
    protected function getImages(array $validated): array
    {
        return $this->getFilesSync($validated, 'images', FileCategory::ID_ASSORTMENT_IMAGE);
    }

    /**
     * @param array $validated
     * @return array
     */
    protected function getProperties(array $validated): array
    {
        return collect(Arr::get($validated, 'properties', []))->mapWithKeys(function ($i) {
            return [$i['uuid'] => ['value' => $i['value']]];
        })->all();
    }

    /**
     * @param array $validated
     * @return array
     */
    protected function getFiles(array $validated): array
    {
        return $this->getFilesSync($validated, 'files', FileCategory::ID_ASSORTMENT_FILE);
    }

    /**
     * @param array $attributes
     * @param string $property
     * @param string $id
     * @return array
     */
    protected function getFilesSync(array $attributes, string $property, string $id): array
    {
        return collect(Arr::get($attributes, $property, []))->mapWithKeys(function ($i) use ($id) {
            return [
                $i['uuid'] => [
                    'file_category_id' => $id,
                    'public_name' => Arr::get($i, 'public_name', null)
                ]
            ];
        })->all();
    }
}
