<?php

namespace App\Http\Resources\API\Suggestions;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class OrganizationSuggestionResource extends JsonResource
{
    /**
     * @param array $suggestion
     * @return array
     */
    public function resource($suggestion)
    {
        return [
            'opf' => Arr::get($suggestion,'data.opf.short'),
            'organization_name' => Arr::get($suggestion, 'data.name.full'),
            'organization_address' => Arr::get($suggestion, 'data.address.value'),
            'inn' => Arr::get($suggestion, 'data.inn'),
            'kpp' => Arr::get($suggestion, 'data.kpp'),
            'ogrn' => Arr::get($suggestion, 'data.ogrn'),
        ];
    }
}
