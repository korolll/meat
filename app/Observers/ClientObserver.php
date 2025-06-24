<?php

namespace App\Observers;

use App\Models\Client;
use App\Models\ClientBonusTransaction;
use App\Services\Management\Client\Bonus\BonusTransactionData;
use App\Services\Management\Client\Bonus\BonusTransactionProducerInterface;

class ClientObserver
{
    /**
     * @param \App\Models\Client $client
     *
     * @return void
     */
    public function created(Client $client)
    {
        $this->applyBonusesForFilledFields($client);
    }

    /**
     * @param \App\Models\Client $client
     */
    public function updated(Client $client)
    {
        $this->applyBonusesForFilledFields($client);
    }

    protected function applyBonusesForFilledFields(Client $client): bool
    {
        $bonusesVal = config('app.clients.bonuses_for_filled_profile');
        if (! $bonusesVal) {
            return false;
        }

        $fieldChanged = $client->isDirty(['email', 'phone', 'birth_date']);
        if (! $fieldChanged || ! $client->email || ! $client->phone || ! $client->birth_date) {
            return false;
        }

        // Check that transaction with bonuses exists
        $transactionExist = $client->clientBonusTransactions()
            ->where('reason', ClientBonusTransaction::REASON_PROFILE_FILLED)
            ->exists();

        if ($transactionExist) {
            return false;
        }

        $data = BonusTransactionData::create()
            ->setClientId($client->uuid)
            ->setBonusDelta($bonusesVal)
            ->setReason(ClientBonusTransaction::REASON_PROFILE_FILLED);

        /** @var BonusTransactionProducerInterface $transactionProducer */
        $transactionProducer = app(BonusTransactionProducerInterface::class);
        $transactionProducer->produce($data);
        return true;
    }
}
