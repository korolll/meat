<?php


namespace App\Services\Integrations\Frontol;


use Illuminate\Http\Request;

interface LoyaltySystemInterface
{
    public function handleDocument(Request $request): array;

    public function handleExtraClient(Request $request): array;

    public function handleClient(Request $request): array;
}
