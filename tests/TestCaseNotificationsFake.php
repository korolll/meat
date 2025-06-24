<?php

namespace Tests;

use Illuminate\Support\Facades\Notification;

abstract class TestCaseNotificationsFake extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }
}
