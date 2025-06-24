<?php

namespace App\Services\Management\Client\Bonus;

use App\Exceptions\ClientExceptions\ClientDoesNotHaveEnoughBonusBalance;
use App\Models\Client;
use App\Models\ClientBonusTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BonusTransactionProducer implements BonusTransactionProducerInterface
{
    /**
     * @inheritDoc
     */
    public function produce(BonusTransactionDataInterface $data): ClientBonusTransaction
    {
        return DB::transaction(function () use ($data) {
            $client = $this->lockClient($data->getClientId());
            $delta = $data->getBonusDelta();

            if ($delta < 0 && $client->bonus_balance < (-$delta)) {
                throw new ClientDoesNotHaveEnoughBonusBalance();
            }

            $transaction = $this->createTransaction(
                $client,
                $delta,
                $data->getReason(),
                $data->getRelatedModel()
            );

            $client->bonus_balance += $delta;
            $client->save();

            return $transaction;
        });
    }

    /**
     * @param string $clientId
     *
     * @return \App\Models\Client
     * @throws \Exception
     */
    protected function lockClient(string $clientId): Client
    {
        $client = Client::query()
            ->lockForUpdate()
            ->find($clientId);

        if (! $client) {
            throw new \Exception('Client not found');
        }

        return $client;
    }

    /**
     * @param \App\Models\Client                       $client
     * @param int                                      $delta
     * @param string|null                              $reason
     * @param \Illuminate\Database\Eloquent\Model|null $related
     *
     * @return \App\Models\ClientBonusTransaction
     */
    protected function createTransaction(Client $client, int $delta, ?string $reason, ?Model $related): ClientBonusTransaction
    {
        $transaction = new ClientBonusTransaction();
        $transaction->forceFill([
            'client_uuid' => $client->uuid,
            'quantity_old' => $client->bonus_balance,
            'quantity_delta' => $delta,
            'quantity_new' => (int)($client->bonus_balance + $delta),
            'reason' => $reason,
        ]);

        if ($related) {
            $transaction->relatedReference()->associate($related);
        }

        $transaction->save();
        return $transaction;
    }
}
