<?php

namespace App\Services\Management\Client\Bonus;

use Illuminate\Database\Eloquent\Model;

class BonusTransactionData implements BonusTransactionDataInterface
{
    protected string $clientId;

    protected ?string $reason = null;

    protected int $bonusDelta;

    protected ?Model $relatedModel = null;

    /**
     * @return static
     */
    public static function create(): self
    {
        return new static();
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     *
     * @return $this
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param string|null $reason
     *
     * @return $this
     */
    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * @return int
     */
    public function getBonusDelta(): int
    {
        return $this->bonusDelta;
    }

    /**
     * @param int $bonusDelta
     *
     * @return $this
     */
    public function setBonusDelta(int $bonusDelta): self
    {
        $this->bonusDelta = $bonusDelta;
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getRelatedModel(): ?Model
    {
        return $this->relatedModel;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|null $relatedModel
     *
     * @return $this
     */
    public function setRelatedModel(?Model $relatedModel): self
    {
        $this->relatedModel = $relatedModel;
        return $this;
    }
}
