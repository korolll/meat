<?php

namespace App\Services\Management\LaboratoryTest;

use App\Contracts\Management\LaboratoryTest\StatusTransitionManagerContract;
use App\Exceptions\ClientExceptions\StatusTransitionImpossibleException;
use App\Models\LaboratoryTest;
use App\Models\User;
use Illuminate\Support\Arr;

class StatusTransitionManager implements StatusTransitionManagerContract
{
    /**
     * @var LaboratoryTest
     */
    protected $laboratoryTest;

    /**
     * @var array
     */
    protected $transitionVariants;

    /**
     * StatusTransitionManager constructor.
     * @param LaboratoryTest $laboratoryTest
     * @param array $transitionVariants
     */
    public function __construct(LaboratoryTest $laboratoryTest, array $transitionVariants)
    {
        $this->laboratoryTest = $laboratoryTest;
        $this->transitionVariants = $transitionVariants;
    }

    /**
     * @param User $user
     * @param string $nextStatusId
     * @return LaboratoryTest|mixed
     * @throws \App\Exceptions\TealsyException
     */
    public function transition(User $user, string $nextStatusId): LaboratoryTest
    {
        if (!$this->checkTransitionAvailable($user, $nextStatusId)) {
            throw new StatusTransitionImpossibleException();
        }

        $this->laboratoryTest->laboratory_test_status_id = $nextStatusId;
        return $this->laboratoryTest;
    }

    /**
     * @param User $user
     * @param string $nextStatusId
     * @return mixed
     */
    protected function checkTransitionAvailable(User $user, string $nextStatusId)
    {
        $available = Arr::get($this->transitionVariants, implode('.', [
            $user->user_type_id,
            $this->laboratoryTest->laboratory_test_status_id,
            $nextStatusId,
        ]));

        return $available !== null;
    }
}
