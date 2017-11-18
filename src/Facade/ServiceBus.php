<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Prooph\ServiceBus\CommandBus commandBus(string $default = 'default')
 * @method static \Prooph\ServiceBus\EventBus eventBus(string $default = 'default')
 * @method static \Prooph\ServiceBus\QueryBus queryBus(string $default = 'default')
 */
final class ServiceBus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'service_bus';
    }
}
