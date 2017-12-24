<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package;

use Barryvdh\Debugbar\LaravelDebugbar;
use Camuthig\ServiceBus\Package\Contracts\ServiceBusManager as ServiceBusManagerContract;
use Camuthig\ServiceBus\Package\Exception\RuntimeException;
use Camuthig\ServiceBus\Package\Plugin\PsrLoggerPlugin;
use Camuthig\ServiceBus\Package\Plugin\ResolverPlugin;
use Illuminate\Contracts\Foundation\Application;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\ServiceBus;
use Prooph\ServiceBus\MessageBus;
use Prooph\ServiceBus\Plugin\MessageFactoryPlugin;
use Prooph\ServiceBus\Plugin\Plugin;
use Prooph\ServiceBus\Plugin\Router\AsyncSwitchMessageRouter;
use Prooph\ServiceBus\Plugin\Router\CommandRouter;
use Prooph\ServiceBus\Plugin\Router\EventRouter;
use Prooph\ServiceBus\Plugin\Router\MessageBusRouterPlugin;
use Prooph\ServiceBus\Plugin\Router\QueryRouter;

class ServiceBusManager implements ServiceBusManagerContract
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var CommandBus[];
     */
    private $commandBuses = [];

    /**
     * @var EventBus[]
     */
    private $eventBuses = [];

    /**
     * @var QueryBus[]
     */
    private $queryBuses = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function commandBus(string $name = 'default'): ServiceBus\CommandBus
    {
        if (!isset($this->commandBuses[$name])) {
            $config = config(sprintf('service_bus.command_buses.%s', $name), null);

            if ($config === null) {
                throw new RuntimeException(sprintf('Unable to find command bus %s', $name));
            }

            $bus = $this->make(CommandBus::class, $name, $config);

            $this->addPlugins($bus, $config);

            if (config('app.debug')) {
                $this->addDebugger($bus);
            }

            $this->addRouter($bus, $config['router'] ?? [], CommandRouter::class);

            $this->commandBuses[$name] = $bus;
        }

        return $this->commandBuses[$name];
    }

    public function eventBus(string $name = 'default'): ServiceBus\EventBus
    {
        if (!isset($this->eventBuses[$name])) {
            $config = config(sprintf('service_bus.event_buses.%s', $name), null);

            if ($config === null) {
                throw new RuntimeException(sprintf('Unable to find command bus %s', $name));
            }

            $bus = $this->make(EventBus::class, $name, $config);

            $this->addPlugins($bus, $config);

            if (config('app.debug')) {
                $this->addDebugger($bus);
            }

            $this->addRouter($bus, $config['router'] ?? [], EventRouter::class);

            $this->eventBuses[$name] = $bus;
        }

        return $this->eventBuses[$name];
    }

    public function queryBus(string $name = 'default'): ServiceBus\QueryBus
    {
        if (!isset($this->queryBuses[$name])) {
            $config = config(sprintf('service_bus.query_buses.%s', $name), null);

            if ($config === null) {
                throw new RuntimeException(sprintf('Unable to find query bus %s', $name));
            }

            $bus = $this->make(QueryBus::class, $name, $config);

            $this->addPlugins($bus, $config);

            if (config('app.debug')) {
                $this->addDebugger($bus);
            }

            $this->addRouter($bus, $config['router'] ?? [], QueryRouter::class);

            $this->queryBuses[$name] = $bus;
        }

        return $this->queryBuses[$name];
    }

    protected function make(string $type, string $name, array $config): MessageBus
    {
        $emitter = $this->app->make($config['action_event_emitter'] ?? ProophActionEventEmitter::class);

        $bus = new $type($emitter);

        switch ($type) {
            case QueryBus::class:
                $bus->setType('query');
                break;
            case EventBus::class:
                $bus->setType('event');
                break;
            case CommandBus::class:
                $bus->setType('command');
                break;
        }

        $bus->setName($name);

        return $bus;
    }

    protected function addRouter(MessageBus $bus, array $config, string $defaultRouter): void
    {
        $type = $config['type'] ?? $defaultRouter;

        /** @var MessageBusRouterPlugin $router */
        $router = new $type($config['routes'] ?? []);

        if ($switchId = $config['async_switch'] ?? null) {
            $asyncSwitch = $this->app->make($switchId);

            $router = new AsyncSwitchMessageRouter($router, $asyncSwitch);
        }

        $router->attachToMessageBus($bus);
    }

    protected function addDebugger(MessageBus $bus): void
    {
        if (class_exists(LaravelDebugbar::class) && config('app.debug')) {
            if ($bus instanceof ServiceBus\CommandBus) {
                $type = 'command';
            } elseif ($bus instanceof ServiceBus\EventBus) {
                $type = 'event';
            } else {
                $type = 'query';
            }

            $debugger = $this->app->make(sprintf('service_bus.debugger.%s_bus', $type));

            $debugger->attachToMessageBus($bus);
        }
    }

    protected function addPlugins(MessageBus $bus, array $config): void
    {
        // Add a message factory plugin to the bus
        $messageFactory = $this->app->make($config['message_factory'] ?? FQCNMessageFactory::class);

        $plugin = new MessageFactoryPlugin($messageFactory);

        $plugin->attachToMessageBus($bus);

        // Add a resolver plugin to the bus
        $resolverPlugin = new ResolverPlugin($this->app);

        $resolverPlugin->attachToMessageBus($bus);

        // Add a logging plugin
        $logger = $this->app->make('log')->getMonolog();

        $loggingPlugin = new PsrLoggerPlugin($logger);

        $loggingPlugin->attachToMessageBus($bus);

        // Add configured plugins
        $plugins = $config['plugins'] ?? [];

        foreach ($plugins as $plugin) {
            /** @var Plugin $plugin */
            $plugin = $this->app->make($plugin);

            $plugin->attachToMessageBus($bus);
        }
    }
}
