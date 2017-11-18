<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Test;

use Camuthig\ServiceBus\Package\Test\Fixture\TestCommand;
use Camuthig\ServiceBus\Package\Test\Fixture\TestHandler;
use Camuthig\ServiceBus\Package\Test\Fixture\TestPlugin;
use Prooph\ServiceBus\CommandBus;

class CommandBusTest extends TestCase
{
    /**
     * @testdox It should support multiple buses
     */
    public function supportsMultipleBuses()
    {
        $serviceBus = app()->make('service_bus');

        $default = $serviceBus->commandBus();
        $secondary = $serviceBus->commandBus('secondary');

        self::assertInstanceOf(CommandBus::class, $default);
        self::assertInstanceOf(CommandBus::class, $secondary);
        self::assertNotSame($default, $secondary);
    }

    /**
     * @testdox It should bind the default command bus to multiple locations
     */
    public function bindsCommandBus()
    {
        $serviceBusCommandBus = app()->make('service_bus')->commandBus();
        $facadeBus            = \Camuthig\ServiceBus\Package\Facade\CommandBus::getFacadeRoot();
        $injectedBus          = app()->make(CommandBus::class);

        self::assertSame($serviceBusCommandBus, $facadeBus);
        self::assertSame($serviceBusCommandBus, $injectedBus);
    }

    /**
     * @testdox It should add bound routers
     */
    public function routesCommands()
    {
        app()->singleton(TestHandler::class);
        $commandHandler = app()->make(TestHandler::class);

        $command = new TestCommand();
        $bus     = app()->make(CommandBus::class);

        $bus->dispatch($command);

        self::assertSame($command, $commandHandler->lastCommand($command));
    }

    /**
     * @testdox It should resolve handlers if not already created
     */
    public function resolvesHandlers()
    {
        $command = new TestCommand();
        $bus     = app()->make(CommandBus::class);

        $bus->dispatch($command);

        self::assertTrue(true);
    }

    /**
     * @testdox It should allow adding custom plugins
     */
    public function supportsPlugins()
    {
        app()->singleton(TestPlugin::class);
        $plugin = app()->make(TestPlugin::class);

        $command = new TestCommand();
        $bus     = app()->make(CommandBus::class);

        $bus->dispatch($command);

        self::assertTrue($plugin->wasFired());
    }
}
