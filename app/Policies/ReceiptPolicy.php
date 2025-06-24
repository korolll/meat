<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ReceiptPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\User $user
     *
     * @return bool|mixed
     */
    public function index(User $user)
    {
        return $user->is_admin;
    }

    /**
     * @param Client $client
     * @return bool
     */
    public function indexOwned(Client $client)
    {
        return true;
    }

    /**
     * @param \Illuminate\Foundation\Auth\User $actor
     * @param \App\Models\Receipt              $receipt
     *
     * @return bool|mixed
     */
    public function view(Authenticatable $actor, Receipt $receipt)
    {
        if ($actor instanceof User) {
            return $actor->is_admin;
        }

        if ($actor instanceof Client) {
            if ($receipt->loyalty_card_type_uuid === null) {
                return false;
            }

            if ($receipt->loyaltyCard->client_uuid === $actor->uuid) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * @param Client $client
     * @param Receipt $receipt
     * @param ReceiptLine $receiptLine
     * @return bool
     */
    public function setRating(Client $client, Receipt $receipt, ReceiptLine $receiptLine)
    {
        if ($this->view($client, $receipt) === false) {
            return false;
        }

        if ($receiptLine->assortment_uuid === null) {
            return false;
        }

        if ($receiptLine->rating()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function reportReceiptsSummaryIndex(User $user)
    {
        return $user->is_store || $user->is_admin;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function salesReport(User $user)
    {
        return $user->is_store || $user->is_admin;
    }
}
