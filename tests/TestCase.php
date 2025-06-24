<?php

namespace Tests;

use App\Services\Integrations\Atol\AtolOnlineClientInterface;
use App\Services\Money\Acquire\AcquireInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @var \Faker\Generator
     */
    protected FakerGenerator $faker;

    /**
     * TestCase constructor.
     *
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->faker = FakerFactory::create();
    }

    /**
     * @return AtolOnlineClientInterface|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockAtolOnlineClientInterface()
    {
        $mock = $this->createMock(AtolOnlineClientInterface::class);
        app()->bind(AtolOnlineClientInterface::class, function () use ($mock) {
            return $mock;
        });
        return $mock;
    }
}
