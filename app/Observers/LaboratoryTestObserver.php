<?php

namespace App\Observers;

use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestStatus;

class LaboratoryTestObserver
{
    /**
     * @param LaboratoryTest $laboratoryTest
     */
    public function updating(LaboratoryTest $laboratoryTest)
    {
        $wasCreated = $laboratoryTest->getOriginal('laboratory_test_status_id') === LaboratoryTestStatus::ID_CREATED;
        $nowNew = $laboratoryTest->laboratory_test_status_id === LaboratoryTestStatus::ID_NEW;

        if ($wasCreated && $nowNew) {
            $laboratoryTest->created_at = now();
        }
    }
}
