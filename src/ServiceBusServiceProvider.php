<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package;

use Barryvdh\Debugbar\LaravelDebugbar;
use Camuthig\ServiceBus\Package\Contracts;
use Camuthig\ServiceBus\Package\Plugin\DataCollectorPlugin;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ServiceBusServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register(): void
    {
        $this->mergeConfig();
        $this->registerBuses();

        if (config('app.debug')) {
            $this->registerDebugPlugins();
        }
    }

    public function boot(): void
    {
        $this->publishes(
            [$this->getConfigPath() => config_path('service_bus.php')],
            'config'
        );

        if (config('app.debug')) {
            $this->addDebugCollectors();
        }
    }

    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'service_bus');
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../config/service_bus.php';
    }

        protected function registerBuses(): void
    {
        $this->app->singleton('service_bus', function (Application $app) {
            return new ServiceBusManager($app);
        });

        $this->app->alias('service_bus', Contracts\ServiceBusManager::class);

        $this->app->singleton(\Prooph\ServiceBus\QueryBus::class, function (Application $app) {
            return $app->make('service_bus')->queryBus();
        });

        $this->app->singleton(\Prooph\ServiceBus\CommandBus::class, function (Application $app) {
            return $app->make('service_bus')->commandBus();
        });

        $this->app->singleton(\Prooph\ServiceBus\EventBus::class, function (Application $app) {
            return $app->make('service_bus')->eventBus();
        });
    }

    /**
     * Register singletons for each of the data collector plugins.
     *
     * @return void
     */
    protected function registerDebugPlugins(): void
    {
        if (class_exists(LaravelDebugbar::class)) {
            $this->app->singleton('service_bus.debugger.command_bus', function (Application $app) {
                return new DataCollectorPlugin($app, 'command');
            });

            $this->app->singleton('service_bus.debugger.event_bus', function (Application $app) {
                return new DataCollectorPlugin($app, 'event');
            });

            $this->app->singleton('service_bus.debugger.query_bus', function (Application $app) {
                return new DataCollectorPlugin($app, 'query');
            });
        }
    }

    /**
     * Add DebugBar controls for each type of message bus.
     *
     * @return void
     */
    protected function addDebugCollectors(): void
    {
        if (class_exists(LaravelDebugbar::class) && config('app.debug')) {
            $debugBar = $this->app->make(LaravelDebugbar::class);

            $debugBar->addCollector($this->app->make('service_bus.debugger.command_bus'));
            $debugBar->addCollector($this->app->make('service_bus.debugger.event_bus'));
            $debugBar->addCollector($this->app->make('service_bus.debugger.query_bus'));
        }
    }

    public function provides()
    {
        return [
            Contracts\ServiceBusManager::class,
            \Prooph\ServiceBus\QueryBus::class,
            \Prooph\ServiceBus\CommandBus::class,
            \Prooph\ServiceBus\EventBus::class,
            'service_bus',
        ];
    }
}
