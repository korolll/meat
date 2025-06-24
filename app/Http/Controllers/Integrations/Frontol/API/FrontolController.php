<?php

namespace App\Http\Controllers\Integrations\Frontol\API;

use App\Http\Controllers\Controller;
use App\Services\Integrations\Frontol\LoyaltySystemInterface;
use Illuminate\Http\Request;

class FrontolController extends Controller
{
    /**
     * @var \App\Services\Integrations\Frontol\LoyaltySystemInterface
     */
    private $loyaltySystem;

    /**
     * FrontolController constructor.
     */
    public function __construct(LoyaltySystemInterface $loyaltySystem)
    {
        $this->middleware('frontol-token:' . config('app.integrations.frontol.token'));
        $this->loyaltySystem = $loyaltySystem;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function document(Request $request): array
    {
        return $this->loyaltySystem->handleDocument($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function extraClient(Request $request): array
    {
        return $this->loyaltySystem->handleExtraClient($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function client(Request $request): array
    {
        return $this->loyaltySystem->handleClient($request);
    }
}
