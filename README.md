# Laravel Service Bus Package

This package supports the use of the prooph service buses inside of a Laravel application.

## Features

- [x] Support default buses that are bound to the classes
- [x] Support unlimited named buses
- [x] Add loggers to all buses
- [x] Automatically resolve handlers
- [x] Collect profiling data in debug mode
- [x] Async switch

## Installation

`composer require camuthig/service-bus-laravel-package`

## Setup

### Publish the Config

`php artisan vendor:publish`

### Include the Provider

The package will automatically be discovered by Laravel when installed, no
changes to include the service provider are needed.

## Usage

### Getting Default Buses

The default instance of each bus type can be retrieved in two ways:

```php
<?php

use \Camuthig\ServiceBus\Package\Test\Fixture\TestEvent;

// Using the facade
\Camuthig\ServiceBus\Package\Facade\EventBus::dispatch(new TestEvent());

// Getting the Prooph interface from the container
app()->make(\Prooph\ServiceBus\EventBus::class)->dispatch(new TestEvent()); 
```

Each bus type (command, event and query) includes both a singleton interface as
well as a facade.

### ServiceBusManager

This package also supports having more than one of each bus type. To leverage a
non-default bus, you will want to use the `ServiceBusManager`. The manager gives
you access to all of the buses by name.

The service manager can be retrieved in three ways: 

```php
<?php

// As a facade
\Camuthig\ServiceBus\Package\Facade\ServiceBus::eventBus('other_bus');

// Getting the interface from the container
$manager = app()->make(\Camuthig\ServiceBus\Package\Contracts\ServiceBusManager::class);
$manager->eventBus('other_bus');

// Getting the `service_bus` from the container
$manager = app()->make('service_bus');
$manager->eventBus('other_bus');

```

## Configuration

The buses can be configured using the `service_bus.php` configuration file. Each
type of bus will have it's own list of of named buses with the following options:

* **message_factory** The service ID or class name of the message factory to use
with the bus. The default is the prooph FQCNMessageFactory if this key is not
provided.
* **action_event_emitter** The service ID or class name of the message factory
to use with the bus. The default is the prooph ProophActionEventEmitter if
this key is not provided.
* **plugins** A list of service IDs or class names of plugins to add to the the
bus.
* **router** The configuration for the bus' router plugin. See details below

### Router configuration

The router for each bus can be configured with:

* **type** The class name of a `MessageBusRouterPlugin` to use for this bus. It
is expected that the constructor for the router should accept an array of
route mappings. See the prooph `CommandRouter`, `EventRouter`, and
`QueryRouter` classes for examples. This will default to the appropriate
prooph router based on the type of the bus if not provided.
* **async_switch** The service ID or class name of an `AsyncSwitchMessageRouter`
to add to the bus. If this value is not provided, no switch will be included
on the bus.
* **routes** A list of route mappings
    * Command and query routing: Each entry in the list of routes is a "message
      name" to "message processor" (either a command handler or query finder).
    * Event routing: Each entry in the list of routes is a "message name" to "list of listeners".
