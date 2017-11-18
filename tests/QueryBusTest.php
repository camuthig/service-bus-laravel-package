<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test;

use Camuthig\ServiceBus\Package\Test\Fixture\TestQuery;
use Camuthig\ServiceBus\Package\Test\Fixture\TestFinder;
use Camuthig\ServiceBus\Package\Test\Fixture\TestPlugin;
use Prooph\ServiceBus\QueryBus;

class QueryBusTest extends TestCase
{
    /**
     * @testdox It should support multiple buses
     */
    public function supportsMultipleBuses()
    {
        $serviceBus = app()->make('service_bus');

        $default   = $serviceBus->queryBus();
        $secondary = $serviceBus->queryBus('secondary');

        self::assertInstanceOf(QueryBus::class, $default);
        self::assertInstanceOf(QueryBus::class, $secondary);
        self::assertNotSame($default, $secondary);
    }

    /**
     * @testdox It should bind the default query bus to multiple locations
     */
    public function bindsQueryBus()
    {
        $serviceBusQueryBus = app()->make('service_bus')->queryBus();
        $facadeBus          = \Camuthig\ServiceBus\Package\Facade\QueryBus::getFacadeRoot();
        $injectedBus        = app()->make(QueryBus::class);

        self::assertSame($serviceBusQueryBus, $facadeBus);
        self::assertSame($serviceBusQueryBus, $injectedBus);
    }

    /**
     * @testdox It should add bound routers
     */
    public function routesQuerys()
    {
        app()->singleton(TestFinder::class);
        $queryFinder = app()->make(TestFinder::class);

        $query = new TestQuery();
        $bus   = app()->make(QueryBus::class);

        $bus->dispatch($query);

        self::assertSame($query, $queryFinder->lastQuery($query));
    }

    /**
     * @testdox It should resolve handlers if not already created
     */
    public function resolvesHandlers()
    {
        $query = new TestQuery();
        $bus   = app()->make(QueryBus::class);

        $bus->dispatch($query);

        self::assertTrue(true);
    }

    /**
     * @testdox It should allow adding custom plugins
     */
    public function supportsPlugins()
    {
        app()->singleton(TestPlugin::class);
        $plugin = app()->make(TestPlugin::class);

        $query = new TestQuery();
        $bus   = app()->make(QueryBus::class);

        $bus->dispatch($query);

        self::assertTrue($plugin->wasFired());
    }
}
