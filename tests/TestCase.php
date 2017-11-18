<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test;

use Camuthig\ServiceBus\Package\Facade\CommandBus;
use Camuthig\ServiceBus\Package\Facade\EventBus;
use Camuthig\ServiceBus\Package\Facade\QueryBus;
use Camuthig\ServiceBus\Package\Facade\ServiceBus;
use Camuthig\ServiceBus\Package\ServiceBusServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceBusServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'ServiceBus' => ServiceBus::class,
            'CommandBus' => CommandBus::class,
            'EventBus'   => EventBus::class,
            'QueryBus'   => QueryBus::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $config = require(__DIR__ . '/Fixture/service_bus.php');

        $app['config']->set('service_bus', $config);
    }
}
