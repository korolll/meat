<?php

namespace App\Services\Models\Client;

use App\Exceptions\ClientExceptions\MaxCartSizeReached;
use App\Models\Assortment;
use App\Models\Client;
use Illuminate\Support\Collection;

class ClientCart implements ClientCartInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $maxSize;

    /**
     * ClientCart constructor.
     */
    public function __construct()
    {
        $this->maxSize = (int)config('app.clients.max_cart_size', 1000);
    }

    /**
     * @param \App\Models\Client $client
     *
     * @return \App\Services\Models\Client\ClientCartInterface
     */
    public function setClient(Client $client): ClientCartInterface
    {
        $this->client = $client;
        $cartData = $client->shopping_cart_data ?: [];
        if ($cartData) {
            $this->data = $cartData['data'];
        } else {
            $this->data = [];
        }

        return $this;
    }

    /**
     * @param string    $assortmentUuid
     * @param float|int $quantity
     *
     * @return \App\Services\Models\Client\ClientCartInterface
     * @throws \App\Exceptions\ClientExceptions\MaxCartSizeReached
     */
    public function add(string $assortmentUuid, float $quantity = 1): ClientCartInterface
    {
        $this->createIfNotExist($assortmentUuid);
        $this->checkMaximumCartSize();
        $this->data[$assortmentUuid]['quantity'] += $quantity;
        return $this;
    }

    /**
     * @param string    $assortmentUuid
     * @param float|int $quantity
     *
     * @return bool
     */
    public function remove(string $assortmentUuid, float $quantity = 1): bool
    {
        if (isset($this->data[$assortmentUuid])) {
            $this->data[$assortmentUuid]['quantity'] -= $quantity;
            if ($this->data[$assortmentUuid]['quantity'] <= 0) {
                return $this->delete($assortmentUuid);
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $assortmentUuid
     *
     * @return bool
     */
    public function delete(string $assortmentUuid): bool
    {
        if (isset($this->data[$assortmentUuid])) {
            unset($this->data[$assortmentUuid]);
            return true;
        }

        return false;
    }

    /**
     * @return \App\Services\Models\Client\ClientCartInterface
     */
    public function clear(): ClientCartInterface
    {
        $this->data = [];
        return $this;
    }

    /**
     * @param string    $assortmentUuid
     * @param float|int $quantity
     *
     * @return \App\Services\Models\Client\ClientCartInterface
     * @throws \App\Exceptions\ClientExceptions\MaxCartSizeReached
     */
    public function update(string $assortmentUuid, float $quantity = 1): ClientCartInterface
    {
        $this->createIfNotExist($assortmentUuid);
        $this->checkMaximumCartSize();
        $this->data[$assortmentUuid]['quantity'] = $quantity;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAssortmentList(): Collection
    {
        $ids = array_keys($this->data);
        $assortments = Assortment::whereIn('uuid', $ids)->get();
        /** @var Assortment $assortment */
        foreach ($assortments as $assortment) {
            $assortment->shopping_cart_quantity = $this->data[$assortment->uuid]['quantity'];
        }

        return $assortments;
    }

    /**
     * @param string $assortmentUuid
     *
     * @return \App\Models\Assortment|null
     */
    public function get(string $assortmentUuid): ?Assortment
    {
        if (! isset($this->data[$assortmentUuid])) {
            return null;
        }

        $assortment = Assortment::findOrFail($assortmentUuid);
        $assortment->shopping_cart_quantity = $this->data[$assortmentUuid]['quantity'];
        return $assortment;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $this->client->shopping_cart_data = [
            'data' => $this->data,
            'updated_at' => now()->format($this->client->getDateFormat())
        ];
        return $this->client->save();
    }

    /**
     * @param string $assortmentId
     */
    protected function createIfNotExist(string $assortmentId): void
    {
        if (! isset($this->data[$assortmentId])) {
            $this->data[$assortmentId] = [
                'assortment_id' => $assortmentId,
                'quantity' => 0
            ];
        }
    }

    /**
     * @throws \App\Exceptions\ClientExceptions\MaxCartSizeReached
     */
    protected function checkMaximumCartSize(): void
    {
        if (count($this->data) > $this->maxSize) {
            throw new MaxCartSizeReached();
        }
    }
}
