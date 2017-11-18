<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void dispatch(mixed $event)
 */
final class EventBus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Prooph\ServiceBus\EventBus::class;
    }
}
