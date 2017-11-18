<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \React\Promise\Promise dispatch(mixed $event)
 */
final class QueryBus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Prooph\ServiceBus\QueryBus::class;
    }
}
