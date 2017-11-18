<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test;

use Camuthig\ServiceBus\Package\Test\Fixture\SecondListener;
use Camuthig\ServiceBus\Package\Test\Fixture\TestEvent;
use Camuthig\ServiceBus\Package\Test\Fixture\FirstListener;
use Camuthig\ServiceBus\Package\Test\Fixture\TestPlugin;
use Prooph\ServiceBus\EventBus;

class EventBusTest extends TestCase
{
    /**
     * @testdox It should support multiple buses
     */
    public function supportsMultipleBuses()
    {
        $serviceBus = app()->make('service_bus');

        $default   = $serviceBus->eventBus();
        $secondary = $serviceBus->eventBus('secondary');

        self::assertInstanceOf(EventBus::class, $default);
        self::assertInstanceOf(EventBus::class, $secondary);
        self::assertNotSame($default, $secondary);
    }

    /**
     * @testdox It should bind the default event bus to multiple locations
     */
    public function bindsEventBus()
    {
        $serviceBusEventBus = app()->make('service_bus')->eventBus();
        $facadeBus          = \Camuthig\ServiceBus\Package\Facade\EventBus::getFacadeRoot();
        $injectedBus        = app()->make(EventBus::class);

        self::assertSame($serviceBusEventBus, $facadeBus);
        self::assertSame($serviceBusEventBus, $injectedBus);
    }

    /**
     * @testdox It should add bound routers with multiple listeners
     */
    public function routesEvents()
    {
        app()->singleton(FirstListener::class);
        $firstListener = app()->make(FirstListener::class);

        app()->singleton(SecondListener::class);
        $secondListener = app()->make(SecondListener::class);

        $event = new TestEvent();
        $bus   = app()->make(EventBus::class);

        $bus->dispatch($event);

        self::assertSame($event, $firstListener->lastEvent($event));
        self::assertSame($event, $secondListener->lastEvent($event));
    }

    /**
     * @testdox It should resolve handlers if not already created
     */
    public function resolvesHandlers()
    {
        $event = new TestEvent();
        $bus   = app()->make(EventBus::class);

        $bus->dispatch($event);

        self::assertTrue(true);
    }

    /**
     * @testdox It should allow adding custom plugins
     */
    public function supportsPlugins()
    {
        app()->singleton(TestPlugin::class);
        $plugin = app()->make(TestPlugin::class);

        $event = new TestEvent();
        $bus   = app()->make(EventBus::class);

        $bus->dispatch($event);

        self::assertTrue($plugin->wasFired());
    }
}
