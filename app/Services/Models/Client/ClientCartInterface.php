<?php

namespace App\Services\Models\Client;

use App\Models\Assortment;
use App\Models\Client;
use Illuminate\Support\Collection;

interface ClientCartInterface
{
    public function setClient(Client $client): self;

    public function add(string $assortmentUuid, float $quantity = 1): self;

    public function delete(string $assortmentUuid): bool;

    public function clear(): self;

    public function remove(string $assortmentUuid, float $quantity = 1): bool;

    public function update(string $assortmentUuid, float $quantity = 1): self;

    public function get(string $assortmentUuid): ?Assortment;

    public function getData(): array;

    public function getAssortmentList(): Collection;

    public function save(): bool;
}
