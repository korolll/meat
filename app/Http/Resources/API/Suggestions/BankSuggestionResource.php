<?php

namespace App\Http\Resources\API\Suggestions;

use App\Services\Framework\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class BankSuggestionResource extends JsonResource
{
    /**
     * @param array $suggestion
     * @return array
     */
    public function resource($suggestion)
    {
        return [
            'name' => Arr::get($suggestion, 'data.name.payment'),
            'identification_code' => Arr::get($suggestion, 'data.bic'),
            'correspondent_account' => Arr::get($suggestion, 'data.correspondent_account'),
        ];
    }
}
