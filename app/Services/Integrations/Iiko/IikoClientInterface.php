<?php

namespace App\Services\Integrations\Iiko;

interface IikoClientInterface
{
    public function getOrganizations(): array;

    public function getMenu(string $organizationId): array;

    public function getStopLists(array $organizationIds): array;

    public function getStopListsMap(array $organizationIds): array;

    public function getPaymentTypes(array $organizationIds): array;

    public function getOrderTypes(array $organizationIds): array;

    public function createOrder(array $data): array;
}
