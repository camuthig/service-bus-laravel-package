<?php

declare(strict_types=1);

namespace Camuthig\ServiceBus\Package\Contracts;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\QueryBus;

interface ServiceBusManager
{
    public function commandBus(string $name = 'default'): CommandBus;

    public function eventBus(string $name = 'default'): EventBus;

    public function queryBus(string $name = 'default'): QueryBus;
}
