<?php

namespace App\Http\Controllers\API;

use App\Contracts\Integrations\DaData\Suggestions\DaDataSuggestionsClientContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Suggestions\MakeSuggestionsRequest;
use App\Http\Resources\API\Suggestions\BankSuggestionResource;
use App\Http\Resources\API\Suggestions\OrganizationSuggestionResource;

class SuggestionController extends Controller
{
    /**
     * @var \App\Contracts\Integrations\DaData\Suggestions\DaDataSuggestionsClientContract
     */
    private $dadata;

    /**
     * @param \App\Contracts\Integrations\DaData\Suggestions\DaDataSuggestionsClientContract $dadata
     */
    public function __construct(DaDataSuggestionsClientContract $dadata)
    {
        $this->dadata = $dadata;
    }

    /**
     * @param \App\Http\Requests\API\Suggestions\MakeSuggestionsRequest $request
     * @return mixed
     */
    public function banks(MakeSuggestionsRequest $request)
    {
        $suggestions = $this->dadata->banks($request->get('query'));

        return BankSuggestionResource::collection($suggestions);
    }

    /**
     * @param \App\Http\Requests\API\Suggestions\MakeSuggestionsRequest $request
     * @return mixed
     */
    public function organizations(MakeSuggestionsRequest $request)
    {
        $suggestions = $this->dadata->organizations($request->get('query'));

        return OrganizationSuggestionResource::collection($suggestions);
    }
}
