<?php

namespace App\Services\Management\Client\Bonus;

use App\Models\ClientBonusTransaction;

interface BonusTransactionProducerInterface
{
    /**
     * @param \App\Services\Management\Client\Bonus\BonusTransactionDataInterface $data
     *
     * @return ClientBonusTransaction
     * @throws \App\Exceptions\ClientExceptions\ClientDoesNotHaveEnoughBonusBalance|\Throwable
     */
    public function produce(BonusTransactionDataInterface $data): ClientBonusTransaction;
}
