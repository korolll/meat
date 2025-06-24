<?php

namespace App\Console\Commands;

use App\Models\LaboratoryTest;
use App\Models\LaboratoryTestStatus;
use Illuminate\Console\Command;

class LaboratoryTestCancelOldCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'laboratory-test:cancel-old';

    /**
     * @var string
     */
    protected $description = 'Отменяет старые лабораторные исследования';

    /**
     * @return void
     */
    public function handle()
    {
        $date = now()->subDays(2);
        LaboratoryTest::new()->where('created_at', '<', $date)->update([
            'laboratory_test_status_id' => LaboratoryTestStatus::ID_CANCELED,
        ]);
    }
}
