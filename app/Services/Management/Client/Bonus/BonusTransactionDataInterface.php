<?php

namespace App\Services\Management\Client\Bonus;

use Illuminate\Database\Eloquent\Model;

interface BonusTransactionDataInterface
{
    public function getClientId(): string;

    public function getReason(): ?string;

    public function getBonusDelta(): int;

    public function getRelatedModel(): ?Model;
}
